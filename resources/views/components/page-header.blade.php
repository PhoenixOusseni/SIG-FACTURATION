@props(['icon', 'title', 'subtitle' => null, 'breadcrumb' => null])

<header class="page-header page-header-pro">
    <div class="page-header-watermark">
        <i data-feather="{{ $icon }}"></i>
    </div>
    <div class="page-header-pattern"></div>

    <div class="container-xl px-4 position-relative">
        <div class="page-header-content">
            <div class="row align-items-center justify-content-between g-3">
                <div class="col-auto">
                    <div class="d-flex align-items-center">
                        <div class="page-header-icon">
                            <i data-feather="{{ $icon }}"></i>
                        </div>
                        <div class="ms-3">
                            @if($breadcrumb)
                                <nav class="page-header-breadcrumb" aria-label="breadcrumb">
                                    {!! $breadcrumb !!}
                                </nav>
                            @endif
                            <h1 class="page-header-title">{{ $title }}</h1>
                            @if($subtitle)
                                <p class="page-header-subtitle">{{ $subtitle }}</p>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-12 col-xl-auto">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>
</header>
