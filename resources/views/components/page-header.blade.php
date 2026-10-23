@props(['icon' => null, 'title', 'subtitle' => null, 'breadcrumb' => null])

@php
    $cleanTitle = preg_replace('/^(Écran|Ecran)\s+[\d\.]+\s*:\s*/iu', '', $title);
@endphp

<div class="container-fluid px-4 pt-4">
    <div class="card mb-4 border-0 shadow-sm" style="background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%); border-radius: 14px; border: 1px solid #e2e8f0;">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    @if($icon)
                        <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(29, 63, 127, 0.08); color: #1d3f7f; display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0;">
                            <i data-feather="{{ $icon }}" style="width: 24px; height: 24px;"></i>
                        </div>
                    @endif
                    <div>
                        @if($breadcrumb)
                            <nav class="page-header-breadcrumb mb-1" aria-label="breadcrumb" style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 600; color: #64748b;">
                                {!! $breadcrumb !!}
                            </nav>
                        @endif
                        <h2 class="fw-bold mb-0 text-dark" style="letter-spacing: -0.5px; font-size: 1.5rem;">
                            {{ $cleanTitle }}
                        </h2>
                        @if($subtitle)
                            <p class="text-muted mb-0 small mt-1">
                                {{ $subtitle }}
                            </p>
                        @endif
                    </div>
                </div>
                @if(isset($slot) && $slot->isNotEmpty())
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        {{ $slot }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
