@if(empty($rows))
    <p class="text-secondary small mb-0">Sem dados ainda.</p>
@else
    <ul class="list-group list-group-flush">
        @foreach($rows as $row)
            <li class="list-group-item d-flex justify-content-between px-0">
                <span class="text-capitalize">{{ str_replace('_', ' ', $row['label']) }}</span>
                <span class="badge bg-secondary">{{ $row['total'] }}</span>
            </li>
        @endforeach
    </ul>
@endif
