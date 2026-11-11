<svg class="storefront-language-flag" viewBox="0 0 60 40" aria-hidden="true" focusable="false">
    @if ($locale === 'en')
        <rect width="60" height="40" fill="#012169" />
        <path d="M0 0 60 40M60 0 0 40" stroke="#fff" stroke-width="9" />
        <path d="M0 0 60 40M60 0 0 40" stroke="#c8102e" stroke-width="4" />
        <path d="M30 0v40M0 20h60" stroke="#fff" stroke-width="14" />
        <path d="M30 0v40M0 20h60" stroke="#c8102e" stroke-width="8" />
    @elseif ($locale === 'id')
        <rect width="60" height="20" fill="#ce1126" />
        <rect y="20" width="60" height="20" fill="#fff" />
    @else
        <path d="M0 0h60v40H0z" fill="#fff" />
        <path d="M0 0h60v4H0zm0 8h60v4H0zm0 8h60v4H0zm0 8h60v4H0zm0 8h60v4H0z" fill="#c8102e" />
        <path d="M0 0h29v22H0z" fill="#010066" />
        <circle cx="13" cy="11" r="7" fill="#ffcc00" />
        <circle cx="16" cy="9" r="6" fill="#010066" />
        <path d="m22 4 1.7 3.5 3.8.5-2.8 2.7.7 3.8-3.4-1.8-3.4 1.8.7-3.8-2.8-2.7 3.8-.5z" fill="#ffcc00" />
    @endif
</svg>
