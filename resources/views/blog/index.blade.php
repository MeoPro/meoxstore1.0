<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

  <head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="{{ $metaDescription }}">
    <meta name="author" content="Meoxstore">
    <link rel="canonical" href="{{ route('blog.posts') }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ __('store.blog_title') }} | Meoxstore">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:url" content="{{ route('blog.posts') }}">
    <link href="https://fonts.googleapis.com/css?family=Roboto:100,100i,300,300i,400,400i,500,500i,700,700i,900,900i&display=swap" rel="stylesheet">

    <title>{{ __('store.blog_title') }} | Meoxstore</title>

    <!-- Bootstrap core CSS -->
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">


    <!-- Additional CSS Files -->
    @vite([
        'resources/css/fontawesome.css',
        'resources/css/templatemo-stand-blog.css',
        'resources/css/owl.css',
        'resources/js/customblog.js',
    ])
<!--

TemplateMo 551 Stand Blog

https://templatemo.com/tm-551-stand-blog

-->
  </head>

  <body class="blog-storefront-page">
    @php($defaultBlogPost = $featuredPosts->first())
    @php($defaultBlogPostUrl = $defaultBlogPost ? route('blog.show', $defaultBlogPost->slug) : route('blog.posts'))

    <!-- ***** Preloader Start ***** -->
    <div id="preloader">
        <div class="jumper">
            <div></div>
            <div></div>
            <div></div>
        </div>
    </div>
    <!-- ***** Preloader End ***** -->

    <!-- Header -->
    <header class="">
      <nav class="navbar navbar-expand-lg">
        <div class="container">
          <a class="navbar-brand" href="{{ route('blog.index') }}"><h2>Meoxstore<em>.</em></h2></a>
          <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
          </button>
          <div class="collapse navbar-collapse" id="navbarResponsive">
            <ul class="navbar-nav ms-auto">
              <li class="nav-item">
                <a class="nav-link" href="{{ route('blog.index') }}">Home
                  <span class="visually-hidden">(current)</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="{{ route('about') }}">About Us</a>
              </li>
              <li class="nav-item active">
                <a class="nav-link" href="{{ route('blog.posts') }}">Blog Entries</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="{{ $defaultBlogPostUrl }}">Post Details</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="{{ route('contact') }}">Contact Us</a>
              </li>
              @guest
                <li class="nav-item">
                  <a class="nav-link" href="{{ route('login') }}">{{ __('store.sign_in') }}</a>
                </li>
              @else
                <li class="nav-item">
                  <a class="nav-link" href="{{ route('profile.edit') }}">{{ __('store.profile') }}</a>
                </li>
                <li class="nav-item">
                  <form class="blog-nav-logout-form" method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="nav-link blog-nav-logout" type="submit">{{ __('store.log_out') }}</button>
                  </form>
                </li>
              @endguest
              <li class="nav-item blog-nav-language">
                <x-storefront-language-switcher />
              </li>
            </ul>
          </div>
        </div>
      </nav>
    </header>

    <!-- Page Content -->
    <!-- Banner Starts Here -->
    <div class="heading-page header-text">
      <section class="page-heading">
        <div class="container">
          <div class="row">
            <div class="col-lg-12">
              <div class="text-content">
                <h4>{{ __('store.blog_latest') }}</h4>
                <h2>{{ __('store.blog_title') }}</h2>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>

    <!-- Banner Ends Here -->

    <section class="call-to-action">
      <div class="container">
        <div class="row">
          <div class="col-lg-12">
            <div class="main-content">
              <div class="row">
                <div class="col-lg-8">
                  <span>{{ __('store.blog') }}</span>
                  <h4>{{ __('store.blog_intro') }}</h4>
                </div>
                <div class="col-lg-4">
                  <div class="main-button">
                    <a href="{{ $defaultBlogPostUrl }}">{{ __('store.blog_read_more') }}</a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>


    <section class="blog-posts grid-system">
      <div class="container">
        <div class="row">
          <div class="col-lg-8">
            <div class="all-blog-posts">
              <div class="row">
                @forelse ($blogPosts as $blogPost)
                  @php($blogCategory = $blogPost->categories->first())
                  @php($blogExcerpt = $blogPost->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($blogPost->body), 180))
                  <div class="col-lg-6">
                    <div class="blog-post">
                      <div class="blog-thumb">
                        @if ($blogPost->cover_image)
                          <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($blogPost->cover_image) }}" alt="{{ $blogPost->title }}" loading="lazy">
                        @else
                          <img src="{{ asset('images/blog-thumb-0'.(($loop->index % 6) + 1).'.jpg') }}" alt="{{ $blogPost->title }}" loading="lazy">
                        @endif
                      </div>
                      <div class="down-content">
                        <span>{{ $blogCategory?->name ?? __('store.blog') }}</span>
                        <a href="{{ route('blog.show', $blogPost->slug) }}"><h4>{{ $blogPost->title }}</h4></a>
                        <ul class="post-info">
                          <li><a href="{{ route('blog.index') }}">Meoxstore</a></li>
                          <li><time datetime="{{ $blogPost->published_at->toAtomString() }}">{{ $blogPost->published_at->format('M j, Y') }}</time></li>
                          <li>
                            @if ($blogCategory)
                              <a href="{{ route('blog.categories.show', $blogCategory->slug) }}">{{ $blogCategory->name }}</a>
                            @else
                              <a href="{{ route('blog.index') }}">{{ __('store.blog') }}</a>
                            @endif
                          </li>
                        </ul>
                        <p>{{ $blogExcerpt }}</p>
                        <div class="post-options">
                          <div class="row">
                            <div class="col-lg-12">
                              <ul class="post-tags">
                                <li><i class="fa fa-tags" aria-hidden="true"></i></li>
                                @forelse ($blogPost->categories as $category)
                                  <li><a href="{{ route('blog.categories.show', $category->slug) }}">{{ $category->name }}</a>@if (! $loop->last),@endif</li>
                                @empty
                                  <li><a href="{{ route('blog.index') }}">{{ __('store.blog') }}</a></li>
                                @endforelse
                              </ul>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                @empty
                  <div class="col-lg-12">
                    <p role="status">{{ $searchQuery !== '' ? __('store.search_no_results') : __('store.blog_empty') }}</p>
                  </div>
                @endforelse
                @if ($blogPosts->hasPages())
                  <div class="col-lg-12">
                    <ul class="page-numbers">
                      @if ($blogPosts->previousPageUrl())
                        <li><a href="{{ $blogPosts->previousPageUrl() }}"><i class="fa fa-angle-double-left" aria-hidden="true"></i></a></li>
                      @endif
                      @foreach ($blogPosts->getUrlRange(max(1, $blogPosts->currentPage() - 2), min($blogPosts->lastPage(), $blogPosts->currentPage() + 2)) as $page => $url)
                        <li @class(['active' => $page === $blogPosts->currentPage()])>
                          <a href="{{ $url }}" @if ($page === $blogPosts->currentPage()) aria-current="page" @endif>{{ $page }}</a>
                        </li>
                      @endforeach
                      @if ($blogPosts->nextPageUrl())
                        <li><a href="{{ $blogPosts->nextPageUrl() }}"><i class="fa fa-angle-double-right" aria-hidden="true"></i></a></li>
                      @endif
                    </ul>
                  </div>
                @endif
              </div>
            </div>
          </div>
          <div class="col-lg-4">
            <div class="sidebar">
              <div class="row">
                <div class="col-lg-12">
                  <div class="sidebar-item search">
                    <form id="search_form" name="gs" method="GET" action="{{ route('blog.posts') }}">
                      <input type="text" name="q" class="searchText" value="{{ $searchQuery }}" placeholder="{{ __('store.search') }}" autocomplete="off" aria-label="{{ __('store.search') }}">
                    </form>
                  </div>
                </div>
                <div class="col-lg-12">
                  <div class="sidebar-item recent-posts">
                    <div class="sidebar-heading">
                      <h2>{{ __('store.blog_latest') }}</h2>
                    </div>
                    <div class="content">
                      <ul>
                        @forelse ($recentBlogPosts as $recentBlogPost)
                          <li><a href="{{ route('blog.show', $recentBlogPost->slug) }}">
                            <h5>{{ $recentBlogPost->title }}</h5>
                            <time datetime="{{ $recentBlogPost->published_at->toAtomString() }}">{{ $recentBlogPost->published_at->format('M j, Y') }}</time>
                          </a></li>
                        @empty
                          <li>{{ __('store.blog_empty') }}</li>
                        @endforelse
                      </ul>
                    </div>
                  </div>
                </div>
                <div class="col-lg-12">
                  <div class="sidebar-item categories">
                    <div class="sidebar-heading">
                      <h2>{{ __('store.blog_categories') }}</h2>
                    </div>
                    <div class="content">
                      <ul>
                        @forelse ($blogCategories as $blogCategory)
                          <li><a href="{{ route('blog.categories.show', $blogCategory->slug) }}">- {{ $blogCategory->name }} ({{ $blogCategory->published_posts_count }})</a></li>
                        @empty
                          <li>{{ __('store.blog_empty') }}</li>
                        @endforelse
                      </ul>
                    </div>
                  </div>
                </div>
                <div class="col-lg-12">
                  <div class="sidebar-item tags">
                    <div class="sidebar-heading">
                      <h2>{{ __('store.blog_categories') }}</h2>
                    </div>
                    <div class="content">
                      <ul>
                        @forelse ($blogCategories as $blogCategory)
                          <li><a href="{{ route('blog.categories.show', $blogCategory->slug) }}">{{ $blogCategory->name }}</a></li>
                        @empty
                          <li>{{ __('store.blog_empty') }}</li>
                        @endforelse
                      </ul>
                    </div>
                  </div>
                  @if ($sidebarBlocks->isNotEmpty())
                    <div class="col-lg-12">
                      @include('blog._sidebar', ['recentBlogPosts' => collect()])
                    </div>
                  @endif
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>


    <footer>
      <div class="container">
        <div class="row">
          <div class="col-lg-12">
            <ul class="social-icons">
              <li><a href="{{ route('home') }}">Meoxstore</a></li>
              <li><a href="{{ route('about') }}">{{ __('store.about_us') }}</a></li>
              <li><a href="{{ route('blog.index') }}">{{ __('store.blog_title') }}</a></li>
              <li><a href="{{ route('shop') }}">{{ __('store.shop') }}</a></li>
              <li><a href="{{ route('contact') }}">{{ __('store.contact_us') }}</a></li>
            </ul>
          </div>
          <div class="col-lg-12">
            <div class="copyright-text">
              <p>Copyright {{ now()->year }} Meoxstore.

                 | Design: <a rel="nofollow" href="https://templatemo.com" target="_parent">TemplateMo</a></p>
            </div>
          </div>
        </div>
      </div>
    </footer>


    <!-- Bootstrap core JavaScript -->
    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.min.js') }}"></script>

    <script language = "text/Javascript">
      cleared[0] = cleared[1] = cleared[2] = 0; //set a cleared flag for each field
      function clearField(t){                   //declaring the array outside of the
      if(! cleared[t.id]){                      // function makes it static and global
          cleared[t.id] = 1;  // you could use true and false, but that's more typing
          t.value='';         // with more chance of typos
          t.style.color='#fff';
          }
      }
    </script>

  </body>
</html>