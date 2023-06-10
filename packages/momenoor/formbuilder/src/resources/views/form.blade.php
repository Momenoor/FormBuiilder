@hasSection('styles')
    @once
        @push('styles')
            {!! $style !!}
        @endpush
    @endonce
@else
    @once
        {!! $style !!}
    @endonce
@endif
@if($showInCard)
    <div class="card">
        <div class="card-header">
            <div class="card-title">Add New Blog</div>
        </div>
        <div class="card-body">
            @endif
            {{ html()->modelForm($model)->attributes($form->getAttributes())->open() }}
            <div class="{{ $rowClass?:'row' }}">
                <div class="col-12">
                    @foreach($fields as $field)
                        {!! $field->render() !!}
                    @endforeach
                </div>
            </div>
            {{ html()->form()->close() }}
            @if($showInCard)
        </div>
    </div>
@endif
@hasSection('scripts')
    @once
        @push('scripts')
            {!! $script !!}
        @endpush
    @endonce
@else
    @once
        {!! $script !!}
    @endonce
@endif
