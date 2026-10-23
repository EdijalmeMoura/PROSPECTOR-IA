<?php

declare(strict_types=1);

namespace Tests\Feature\Approval;

use App\Enums\ApprovalStatus;
use App\Enums\ChannelType;
use App\Models\Approval;
use App\Models\Lead;
use App\Models\OptOut;
use App\Models\User;
use App\Services\Approval\ApprovalService;
use App\Services\Communication\CommunicationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApprovalWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->admin()->create();
    }

    public function test_new_draft_is_pending_and_bound_to_content_hash(): void
    {
        $lead = Lead::factory()->create();
        $approval = app(ApprovalService::class)->createDraft(
            $lead, ChannelType::Email, 'cliente@empresa.com', 'Mensagem com conteúdo suficiente para aprovação.'
        );

        $this->assertSame(ApprovalStatus::Pendente, $approval->status);
        $this->assertSame(1, $approval->content_version);
        $this->assertSame(
            Approval::hashContent(ChannelType::Email, 'cliente@empresa.com', 'Mensagem com conteúdo suficiente para aprovação.'),
            $approval->content_hash,
        );
    }

    public function test_editing_creates_new_version_and_supersedes_previous(): void
    {
        $lead = Lead::factory()->create();
        $service = app(ApprovalService::class);
        $first = $service->createDraft($lead, ChannelType::Email, 'a@empresa.com', 'Primeira versão do texto da mensagem comercial.');
        $service->approve($first, $this->user);

        $second = $service->createDraft($lead, ChannelType::Email, 'a@empresa.com', 'Segunda versão do texto da mensagem comercial.');

        $this->assertSame(2, $second->content_version);
        $this->assertSame(ApprovalStatus::Substituida, $first->fresh()->status);
        $this->assertSame(ApprovalStatus::Pendente, $second->status);
    }

    public function test_send_is_blocked_without_valid_approval(): void
    {
        $lead = Lead::factory()->create();
        $approval = app(ApprovalService::class)->createDraft($lead, ChannelType::Email, 'a@empresa.com', 'Texto aguardando aprovação humana ainda.');

        $result = app(CommunicationService::class)->send($approval);

        $this->assertFalse($result['ok']);
        $this->assertStringContainsString('aprovada', $result['reason']);
        $this->assertDatabaseCount('message_logs', 0);
    }

    public function test_tampered_content_blocks_sending(): void
    {
        $lead = Lead::factory()->create();
        $service = app(ApprovalService::class);
        $approval = $service->createDraft($lead, ChannelType::Email, 'a@empresa.com', 'Texto original aprovado pelo responsável humano.');
        $service->approve($approval, $this->user);

        $approval->forceFill(['full_text' => 'Texto alterado depois da aprovação sem nova revisão.'])->save();

        $result = app(CommunicationService::class)->send($approval->fresh());

        $this->assertFalse($result['ok']);
        $this->assertStringContainsString('alterado', $result['reason']);
    }

    public function test_opt_out_blocks_sending_even_with_approved_message(): void
    {
        $lead = Lead::factory()->create();
        $service = app(ApprovalService::class);
        $approval = $service->createDraft($lead, ChannelType::Email, 'nao-quero@empresa.com', 'Mensagem de prospecção comercial devidamente aprovada.');
        $service->approve($approval, $this->user);

        OptOut::create([
            'channel' => 'email', 'identifier' => 'nao-quero@empresa.com',
            'source' => 'titular', 'requested_at' => now(),
        ]);

        $result = app(CommunicationService::class)->send($approval->fresh());

        $this->assertFalse($result['ok']);
        $this->assertStringContainsString('opt-out', $result['reason']);
    }

    public function test_unconfigured_channel_does_not_mark_message_as_sent(): void
    {
        config(['mail.default' => 'log']);
        $lead = Lead::factory()->create();
        $service = app(ApprovalService::class);
        $approval = $service->createDraft($lead, ChannelType::Email, 'ok@empresa.com', 'Mensagem aprovada mas canal de e-mail não está configurado.');
        $service->approve($approval, $this->user);

        $result = app(CommunicationService::class)->send($approval->fresh());

        $this->assertFalse($result['ok']);
        $this->assertStringContainsString('não configurado', $result['reason']);
        $this->assertDatabaseHas('message_logs', ['status' => 'falha']);
    }

    public function test_approval_http_routes_require_authentication(): void
    {
        $this->get(route('approvals.index'))->assertRedirect(route('login'));
    }
}
