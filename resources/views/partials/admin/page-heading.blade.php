@if (View::hasSection('page_title'))
    <div class="page-heading">
        <div class="page-heading-copy">
            @hasSection('page_icon')
                <span class="page-icon"><i class="bi {{ trim($__env->yieldContent('page_icon')) }}" aria-hidden="true"></i></span>
            @endif
            <div>
                @hasSection('page_eyebrow')
                    <p class="eyebrow mb-1">@yield('page_eyebrow')</p>
                @endif
                <h1 class="h3 mb-1">@yield('page_title')</h1>
                @hasSection('page_subtitle')
                    <p class="text-muted mb-0">@yield('page_subtitle')</p>
                @endif
            </div>
        </div>
        @hasSection('heading_actions')
            <div class="heading-actions">@yield('heading_actions')</div>
        @endif
    </div>
@endif
