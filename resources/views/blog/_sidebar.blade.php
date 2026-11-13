@if ($recentBlogPosts->isNotEmpty() || $sidebarBlocks->isNotEmpty())
    <aside class="sidebar" aria-label="{{ __('store.blog_sidebar') }}">
        @if ($recentBlogPosts->isNotEmpty())
            <section class="sidebar-item recent-posts">
                <div class="sidebar-heading">
                    <h2>{{ __('store.blog_latest') }}</h2>
                </div>
                <div class="content">
                    <ul>
                        @foreach ($recentBlogPosts as $recentBlogPost)
                            <li>
                                <a href="{{ route('blog.show', $recentBlogPost->slug) }}">
                                    <h5>{{ $recentBlogPost->title }}</h5>
                                    <span><time datetime="{{ $recentBlogPost->published_at->toAtomString() }}">{{ $recentBlogPost->published_at->format('M j, Y') }}</time></span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </section>
        @endif

        @foreach ($sidebarBlocks as $block)
            <section @class([
                'sidebar-item',
                'categories' => in_array($block->type, [
                    \App\Models\BlogSidebarBlock::TYPE_BLOG_CATEGORIES,
                    \App\Models\BlogSidebarBlock::TYPE_GAME_CATEGORIES,
                    \App\Models\BlogSidebarBlock::TYPE_ITEM_CATEGORIES,
                ], true),
            ])>
                <div class="sidebar-heading">
                    <h2>{{ $block->title }}</h2>
                </div>

                @if ($block->type === \App\Models\BlogSidebarBlock::TYPE_PRODUCT && $block->product)
                    <a class="blog-sidebar-product" href="{{ route('product-details.show', $block->product) }}">
                        @if ($block->product->image)
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($block->product->image) }}" alt="" loading="lazy">
                        @endif
                        <span>{{ $block->product->name }}</span>
                        <span class="blog-sidebar-link">{{ __('store.view_product', ['product' => $block->product->name]) }} →</span>
                    </a>
                @elseif ($block->type === \App\Models\BlogSidebarBlock::TYPE_POSTER && $block->image_path)
                    @if ($block->link_url)
                        <a class="blog-sidebar-poster" href="{{ $block->link_url }}" target="_blank" rel="noopener noreferrer">
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($block->image_path) }}" alt="{{ $block->title }}" loading="lazy">
                        </a>
                    @else
                        <div class="blog-sidebar-poster">
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($block->image_path) }}" alt="{{ $block->title }}" loading="lazy">
                        </div>
                    @endif
                @elseif ($block->type === \App\Models\BlogSidebarBlock::TYPE_BLOG_CATEGORIES)
                    <div class="content">
                        <ul class="blog-sidebar-links">
                            @foreach ($sidebarBlogCategories as $category)
                                <li><a href="{{ route('blog.categories.show', $category->slug) }}">{{ $category->name }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                @elseif ($block->type === \App\Models\BlogSidebarBlock::TYPE_GAME_CATEGORIES)
                    <div class="content">
                        <ul class="blog-sidebar-links">
                            @foreach ($sidebarGameCategories as $category)
                                <li><a href="{{ route('shop', ['game' => $category->id]) }}">{{ $category->name }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                @elseif ($block->type === \App\Models\BlogSidebarBlock::TYPE_ITEM_CATEGORIES)
                    <div class="content">
                        <ul class="blog-sidebar-links">
                            @foreach ($sidebarItemCategories as $category)
                                <li><a href="{{ route('shop', ['item_category' => $category->id]) }}">{{ $category->localizedName() }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </section>
        @endforeach
    </aside>
@endif
