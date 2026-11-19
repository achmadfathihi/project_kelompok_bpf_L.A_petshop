<div class="col-xl-3 col-md-6 mb-4">
    <div class="card card-stats">
        <div class="card-body">
            <div class="row">
                <div class="col">
                    <h5 class="card-title text-uppercase text-muted mb-0">{{ $label }}</h5>
                    <span class="h2 font-weight-bold mb-0">{{ $value }}</span>
                    @isset($note) <div class="small text-muted mt-1">{{ $note }}</div> @endisset
                </div>
                <div class="col-auto">
                    <div class="icon icon-shape {{ $color }} text-white rounded-circle shadow"><i class="{{ $icon }}"></i></div>
                </div>
            </div>
        </div>
    </div>
</div>