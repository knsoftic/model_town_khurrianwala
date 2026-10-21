{{--
    SEO guide content, shared by the admin page and the private link ($checks, $pages from App\Services\SeoGuide).
--}}
@php
    $host = request()->getHost();
    $home = route('home');
    $sitemap = route('sitemap');
    $robotsTxt = url('robots.txt');
    $phone = setting('phone');
    $done = collect($checks)->where('ok', true)->count();
    $seoSettings = route('admin.settings.edit', 'seo');
    $verification = route('admin.settings.edit', 'verification');

    $automatic = [
        'Title, meta description and keywords on every page — from the page’s SEO box, or the website defaults',
        'Robots meta tag and X-Robots-Tag header: '.app(\App\Services\SeoService::class)->defaultRobots(),
        'Canonical link on every page, pointing to the page itself',
        'Author and publisher tags',
        'Open Graph and Twitter tags — the title, text and photo shown when a link is shared on WhatsApp or Facebook',
        'Local search tags (geo.region, geo.position) and the map pin of the society',
        'Structured data for Google: business (RealEstateAgent), WebSite, breadcrumbs, plots (Product), FAQ and location (Place)',
        'XML sitemap, updated by itself when plots or pages change',
        'robots.txt with the sitemap link, and llms.txt for AI search assistants',
        'Alt text on images, lazy loading and resized WebP images',
        'Mobile-friendly pages with inline CSS for a fast first paint',
        'Admin panel hidden from search engines',
    ];

    $onPage = [
        [
            'title' => 'Website title, description and keywords',
            'text' => 'Open Settings → SEO & Scripts. This is what Google shows for the homepage, and the fallback for every other page.',
            'points' => [
                'Title: 50–60 characters, main keyword first — e.g. “Model Town Khurrianwala – Plots on Installments Faisalabad”.',
                'Description: 140–160 characters. Say what you offer, where, and give the phone number.',
                'Keywords: type a phrase buyers search for and press Add — e.g. “5 marla plot for sale in Faisalabad”. Remove one with ×.',
            ],
            'links' => [['SEO settings', $seoSettings, true]],
        ],
        [
            'title' => 'Each page’s own title and description',
            'text' => 'Website Content → Pages → Edit next to a page → “SEO” box → Edit SEO.',
            'points' => [
                'Every page needs a different title and description. End the title with “| Model Town Khurrianwala”.',
                'Add 3–5 keywords that fit that page only (e.g. Installment Plans: “plots on installments in Faisalabad”).',
                'Leave “Canonical URL” empty and “Robots” on Default. Choose noindex only for a page Google should not show.',
            ],
            'links' => [['Pages', route('admin.pages.index'), true]],
        ],
        [
            'title' => 'Plot pages',
            'text' => 'Plots → All Plot Sizes → Edit → SEO box. Each plot size has its own page in Google.',
            'points' => [
                'Title like “5 Marla Plot for Sale in Faisalabad”. Put the price, down payment and monthly installment in the description.',
                'Upload real photos of the plots; the plot name is used as their alt text.',
            ],
            'links' => [['All Plot Sizes', route('admin.plots.index'), true]],
        ],
        [
            'title' => 'Location page',
            'text' => 'Website Content → Locations → Edit. Keep the address and map pin exactly the same as on Google Business Profile (Off-page step 1).',
            'links' => [['Locations', route('admin.locations.index'), true]],
        ],
        [
            'title' => 'Headings and page text',
            'text' => 'Google reads the words on the page. Pages with useful text rank higher than pages with only pictures.',
            'points' => [
                'Each page has one main heading (the page heading). Keep the main keyword in it.',
                'Write at least 300 words on the About and Plots pages: location, amenities, sizes, payment plan, possession.',
                'Use keywords naturally. Repeating the same phrase again and again hurts ranking.',
            ],
            'links' => [['Homepage Sections', route('admin.sections.index'), true], ['Pages', route('admin.pages.index'), true]],
        ],
        [
            'title' => 'Photos',
            'text' => 'Website Content → Gallery. The photo title becomes its alt text, which Google Images reads.',
            'points' => [
                'Give every photo a clear title, e.g. “Main gate of Model Town Khurrianwala, Jhumra Road”.',
                'Name files before uploading (model-town-khurrianwala-main-gate.jpg) and keep them under 500 KB.',
            ],
            'links' => [['Gallery', route('admin.gallery.index'), true]],
        ],
        [
            'title' => 'Videos',
            'text' => 'Website Content → Videos. Give every video a title and description with a keyword. Videos on YouTube also bring visitors from YouTube search.',
            'links' => [['Videos', route('admin.videos.index'), true]],
        ],
        [
            'title' => 'FAQs',
            'text' => 'Website Content → FAQs. Add the questions buyers ask on the phone — Google can show them under your result.',
            'points' => ['e.g. “What is the price of a 5 marla plot?”, “Is there a booking fee?”, “When is possession?”'],
            'links' => [['FAQs', route('admin.faqs.index'), true]],
        ],
        [
            'title' => 'Links between pages',
            'text' => 'Website Content → Menus. Keep Plots, Installment Plans, Location and Contact in the header and footer menus so Google finds every page.',
            'links' => [['Menus', route('admin.menus.index'), true]],
        ],
        [
            'title' => 'Find more keywords',
            'text' => 'Look at what people actually search for, then add those phrases to the website keywords and page text.',
            'points' => [
                'Type “plots in Faisalabad” in Google and read the suggestions and the “People also ask” box.',
                'Search Console → Performance → Queries shows the searches you already appear for (after Technical step 2).',
                'Google Trends (Pakistan) compares phrases, e.g. “plot on installment” vs “plots for sale”.',
            ],
            'links' => [['Google Trends — Pakistan', 'https://trends.google.com/trends/explore?geo=PK&q=plots%20in%20faisalabad', false], ['Keyword Planner', 'https://ads.google.com/home/tools/keyword-planner/', false]],
        ],
        [
            'title' => 'Check your work',
            'text' => 'Open a page, right-click → View page source, and look for the title, description, keywords and robots tags. Or use the “Page by page check” below — it updates as soon as you save.',
            'links' => [['Open website', $home, false]],
        ],
    ];

    $technical = [
        [
            'title' => 'Secure address (https) and one website address',
            'text' => 'aaPanel → Website → '.$host.' → SSL → Let’s Encrypt → Apply, then switch on Force HTTPS. Choose one address (with or without www) and redirect the other one to it (aaPanel → Redirect, 301).',
            'points' => ['In the server .env file set the line below, then run the server command again.'],
            'code' => 'APP_URL=https://'.$host,
        ],
        [
            'title' => 'Connect Google Search Console',
            'text' => 'Search Console → Add property → “URL prefix” → https://'.$host.' → choose “HTML tag” → copy the tag. Paste it in Settings → Verification & Analytics → Google Search Console → Save. Go back to Search Console and click Verify.',
            'links' => [['Google Search Console', 'https://search.google.com/search-console', false], ['Verification settings', $verification, true]],
        ],
        [
            'title' => 'Submit the sitemap',
            'text' => 'Search Console → Sitemaps → type the line below → Submit. After a day the status should say “Success”.',
            'code' => 'sitemap.xml',
            'links' => [['Open sitemap', $sitemap, false]],
        ],
        [
            'title' => 'Ask Google to index the main pages',
            'text' => 'Search Console → URL inspection (top search box) → paste a page address → Request indexing. Do it for the homepage, Plots, Installment Plans, every plot size, Location and Contact — and again after you change a page.',
        ],
        [
            'title' => 'Connect Bing (also covers Yahoo and DuckDuckGo)',
            'text' => 'Bing Webmaster Tools → sign in → “Import from Google Search Console” (fastest). Or add the site, choose “HTML Meta Tag” and paste it in Settings → Verification & Analytics → Bing. Submit the sitemap there too.',
            'links' => [['Bing Webmaster Tools', 'https://www.bing.com/webmasters', false], ['Verification settings', $verification, true]],
        ],
        [
            'title' => 'Connect Google Analytics',
            'text' => 'Google Analytics → Admin → Create property → Web → '.$host.' → copy the Measurement ID (G-XXXXXXXXXX). Paste it in Settings → Verification & Analytics → Google Analytics → Save. In Analytics, link Search Console under Admin → Product links.',
            'links' => [['Google Analytics', 'https://analytics.google.com/', false], ['Verification settings', $verification, true]],
        ],
        [
            'title' => 'Check the speed',
            'text' => 'PageSpeed Insights → test the homepage and one plot page on Mobile. Aim for 90 or more.',
            'points' => [
                'Compress photos before uploading; keep “Load analytics after the first scroll or tap” on.',
                'Run the server command after every update — it includes the speed script and image optimisation.',
            ],
            'links' => [['PageSpeed Insights', 'https://pagespeed.web.dev/', false]],
        ],
        [
            'title' => 'Test the structured data',
            'text' => 'Rich Results Test → test the homepage, the FAQ page and a plot page. Errors must be fixed; warnings are fine.',
            'links' => [['Rich Results Test', 'https://search.google.com/test/rich-results', false], ['Schema validator', 'https://validator.schema.org/', false]],
        ],
        [
            'title' => 'Keep robots.txt open',
            'text' => 'robots.txt should show “Disallow:” with nothing after it (everything allowed) and the Sitemap line. Keep “Show this website in search engines” on in Settings → SEO.',
            'links' => [['Open robots.txt', $robotsTxt, false], ['SEO settings', $seoSettings, true]],
        ],
        [
            'title' => 'Weekly check in Search Console',
            'text' => 'Look at Pages (why a page is not indexed), Core Web Vitals, HTTPS, and Security & Manual actions (both should be empty). If you delete or rename a page, set up a redirect so old links do not end on “page not found”.',
        ],
        [
            'title' => 'See what Google has indexed',
            'text' => 'Search for the line below on Google. Every important page should be listed.',
            'code' => 'site:'.$host,
            'links' => [['Search Google', 'https://www.google.com/search?q='.urlencode('site:'.$host), false]],
        ],
    ];

    $offPage = [
        [
            'title' => 'Google Business Profile — the most important step',
            'text' => 'This puts the society on Google Maps and in the map box for searches like “housing society near me” and “plots in Faisalabad”.',
            'points' => [
                'business.google.com → Add business → “Model Town Khurrianwala” → category e.g. Housing society or Real estate developer.',
                'Exact address and map pin (Main Bazar Khurrianwala, Jhumra Road), phone '.$phone.', website https://'.$host.'.',
                'Verify (postcard, phone or video), then add 20+ real photos, opening hours and the plot sizes as products.',
                'Post an update or offer every week.',
            ],
            'links' => [['Google Business Profile', 'https://business.google.com/', false]],
        ],
        [
            'title' => 'Google reviews',
            'text' => 'Ask every buyer and site visitor for a review — send them the review link from your Business Profile. Reply to every review, good or bad. Never buy fake reviews.',
        ],
        [
            'title' => 'Same name, address and phone everywhere',
            'text' => 'Write the business name, address and phone number exactly as on the website footer on every listing and profile. Google trusts details that match.',
        ],
        [
            'title' => 'Property portals',
            'text' => 'List each plot size on Zameen.com, Graana.com and OLX with real photos, the price and installment plan, and the website link in the description.',
            'links' => [['Zameen.com', 'https://www.zameen.com/', false], ['Graana.com', 'https://www.graana.com/', false], ['OLX', 'https://www.olx.com.pk/', false]],
        ],
        [
            'title' => 'Social media profiles',
            'text' => 'Facebook page, Instagram, YouTube and TikTok: put the website link in the bio, post site progress every week, and add each profile in Settings → Social Links so Google connects them to the website.',
            'links' => [['Social Links', route('admin.social-links.index'), true]],
        ],
        [
            'title' => 'YouTube videos',
            'text' => 'Upload a site tour, plot visits and development updates.',
            'points' => [
                'Title like “Model Town Khurrianwala Faisalabad – 5 Marla Plot on Installments | Site Tour”.',
                'Website link in the first line of the description; then add the video under Videos on the website.',
            ],
        ],
        [
            'title' => 'More map and business listings',
            'text' => 'Add the society to Bing Places and Apple Business Connect, and to trusted Pakistani business directories — with the same details.',
            'links' => [['Bing Places', 'https://www.bingplaces.com/', false], ['Apple Business Connect', 'https://businessconnect.apple.com/', false]],
        ],
        [
            'title' => 'Links from other websites (backlinks)',
            'text' => 'Ask Faisalabad news sites, property blogs and dealers who sell your plots to link to the website, e.g. with a news story about a development milestone.',
            'points' => ['One link from a real local website is worth more than hundreds of spam links. Never buy link packages — Google can push the website down for it.'],
        ],
        [
            'title' => 'WhatsApp and Facebook groups',
            'text' => 'Share page links (Plots, Installment Plans) and your short link in Faisalabad property groups, so visitors land on the website, not only on a phone number.',
            'links' => [['Short link settings', route('admin.settings.edit', 'redirect'), true]],
        ],
        [
            'title' => 'Measure the results',
            'text' => 'Search Console → Performance shows searches, clicks and position; Links shows who links to you. Business Profile → Performance shows calls and direction requests.',
            'links' => [['Google Search Console', 'https://search.google.com/search-console', false]],
        ],
    ];

    $routine = [
        'Every week' => ['Post on Google Business Profile and social media (photos of development work).', 'Reply to new Google reviews.', 'Search Console: check Performance and fix new errors under Pages.'],
        'Every month' => ['Open this guide and fix anything marked red.', 'Add 1–2 new FAQs or more page text.', 'Test speed on PageSpeed Insights.', 'Update prices and plans on the website and the property portals together.'],
        'After every website update' => ['Run the server command.', 'Request indexing for pages that changed.'],
    ];
@endphp

<nav class="scrollbar-none -mx-4 mb-6 flex gap-2 overflow-x-auto px-4 lg:mx-0 lg:px-0" aria-label="Guide sections">
    @foreach (['status' => 'SEO status', 'on-page' => 'On-page SEO', 'pages' => 'Page by page check', 'technical' => 'Technical SEO', 'off-page' => 'Off-page SEO', 'routine' => 'Routine', 'links' => 'Useful links'] as $anchor => $label)
        <a href="#{{ $anchor }}" class="shrink-0 rounded-full border border-slate-200 bg-white px-3.5 py-1.5 text-sm font-medium text-slate-700 shadow-xs transition hover:border-slate-300 hover:text-slate-900">{{ $label }}</a>
    @endforeach
</nav>

<div class="space-y-6">
    <section id="status" class="scroll-mt-24">
        <div class="grid gap-6 xl:grid-cols-2">
            <x-admin.card title="Your SEO checklist" :description="$done.' of '.count($checks).' done. Red items need you — each has a button to the right place.'" :padding="false">
                <ul class="divide-y divide-slate-100">
                    @foreach ($checks as $check)
                        <li class="flex items-start gap-3 px-5 py-3.5">
                            <x-icon :name="$check['ok'] ? 'circle-check' : 'circle-x'" :class="$check['ok'] ? 'mt-0.5 size-5 text-emerald-600' : 'mt-0.5 size-5 text-rose-500'" />
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-semibold text-slate-900">{{ $check['label'] }}</p>
                                <p class="mt-0.5 text-xs leading-relaxed text-slate-500">{{ $check['detail'] }}</p>
                            </div>
                            @unless ($check['ok'])
                                <a href="{{ $check['url'] }}" @unless (str_starts_with($check['url'], '#')) target="_blank" rel="noopener" @endunless class="a-btn-secondary a-btn-sm shrink-0">{{ $check['action'] }}</a>
                            @endunless
                        </li>
                    @endforeach
                </ul>
            </x-admin.card>

            <x-admin.card title="Already done by the website" description="On-page SEO that works on every page automatically — nothing to do.">
                <ul class="space-y-2.5">
                    @foreach ($automatic as $item)
                        <li class="flex gap-2.5 text-sm text-slate-700">
                            <x-icon name="circle-check" class="mt-0.5 size-4 text-emerald-600" /> <span>{{ $item }}</span>
                        </li>
                    @endforeach
                </ul>
            </x-admin.card>
        </div>
    </section>

    <section id="on-page" class="scroll-mt-24">
        <x-admin.card title="On-page SEO — step by step" description="What is on your own pages: titles, descriptions, keywords, text and photos. Buttons with a lock open the admin panel (sign in first).">
            @include('admin.seo-guide._steps', ['steps' => $onPage])
        </x-admin.card>
    </section>

    <section id="pages" class="scroll-mt-24">
        <x-admin.card title="Page by page check" description="The title and description Google gets for each page right now. Titles: 30–65 characters. Descriptions: 110–165 characters." :padding="false">
            <div class="overflow-x-auto">
                <table class="a-table">
                    <thead>
                        <tr>
                            <th>Page</th>
                            <th>Title &amp; description in Google</th>
                            <th>Keywords</th>
                            <th>Status</th>
                            <th class="text-right">Edit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pages as $row)
                            <tr>
                                <td class="whitespace-nowrap">
                                    <a href="{{ $row['url'] }}" target="_blank" rel="noopener" class="font-semibold text-slate-900 hover:underline">{{ $row['name'] }}</a>
                                </td>
                                <td class="min-w-[22rem]">
                                    <p class="font-medium text-sky-800">{{ $row['title'] }} <span class="text-xs font-normal text-slate-400">({{ mb_strlen($row['title']) }})</span></p>
                                    <p class="mt-0.5 text-xs leading-relaxed text-slate-500">{{ $row['description'] ?: '—' }} @if ($row['description'])<span class="text-slate-400">({{ mb_strlen($row['description']) }})</span>@endif</p>
                                </td>
                                <td class="whitespace-nowrap text-xs text-slate-600">
                                    {{ $row['keywords'] }} <span class="text-slate-400">{{ $row['own_keywords'] ? 'own' : 'website' }}</span>
                                </td>
                                <td>
                                    @if ($row['ok'])
                                        <span class="a-badge bg-emerald-50 text-emerald-700 ring-emerald-600/20">Good</span>
                                    @else
                                        <span class="a-badge bg-amber-50 text-amber-800 ring-amber-600/20">Check</span>
                                        <ul class="mt-1 space-y-0.5 text-xs text-amber-800">
                                            @foreach ($row['issues'] as $issue)
                                                <li>{{ $issue }}</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <a href="{{ $row['edit'] }}" target="_blank" rel="noopener" class="a-btn-ghost a-btn-sm"><x-icon name="lock" class="size-3.5 text-slate-400" /> Edit</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-admin.card>
    </section>

    <section id="technical" class="scroll-mt-24">
        <x-admin.card title="Technical SEO — your flow" description="Do these once, in this order. They let Google find, trust and measure the website.">
            @include('admin.seo-guide._steps', ['steps' => $technical])
        </x-admin.card>
    </section>

    <section id="off-page" class="scroll-mt-24">
        <x-admin.card title="Off-page SEO" description="Work outside the website that makes Google trust it more: maps, reviews, listings, social media and links from other websites.">
            @include('admin.seo-guide._steps', ['steps' => $offPage])
        </x-admin.card>
    </section>

    <section id="routine" class="scroll-mt-24">
        <x-admin.card title="Routine" description="SEO keeps working when it is done a little, regularly.">
            <div class="grid gap-5 md:grid-cols-3">
                @foreach ($routine as $when => $tasks)
                    <div class="rounded-lg border border-slate-200 p-4">
                        <p class="text-sm font-semibold text-slate-900">{{ $when }}</p>
                        <ul class="mt-2 space-y-2">
                            @foreach ($tasks as $task)
                                <li class="flex gap-2 text-sm text-slate-600"><x-icon name="list-checks" class="mt-0.5 size-4 text-slate-400" /> <span>{{ $task }}</span></li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        </x-admin.card>
    </section>

    <section id="links" class="scroll-mt-24">
        <x-admin.card title="Useful links">
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ([
                    ['Sitemap', $sitemap, 'file-search'],
                    ['robots.txt', $robotsTxt, 'file-search'],
                    ['llms.txt (AI search)', url('llms.txt'), 'file-search'],
                    ['Google Search Console', 'https://search.google.com/search-console', 'external-link'],
                    ['Google Business Profile', 'https://business.google.com/', 'external-link'],
                    ['PageSpeed Insights', 'https://pagespeed.web.dev/', 'external-link'],
                    ['Rich Results Test', 'https://search.google.com/test/rich-results', 'external-link'],
                    ['Bing Webmaster Tools', 'https://www.bing.com/webmasters', 'external-link'],
                    ['Google Analytics', 'https://analytics.google.com/', 'external-link'],
                ] as [$label, $url, $icon])
                    <a href="{{ $url }}" target="_blank" rel="noopener" class="flex min-w-0 items-center gap-3 rounded-lg border border-slate-200 px-3.5 py-3 transition hover:border-slate-300 hover:bg-slate-50">
                        <x-icon :name="$icon" class="size-4 text-slate-400" />
                        <span class="min-w-0">
                            <span class="block text-sm font-semibold text-slate-900">{{ $label }}</span>
                            <span class="block truncate text-xs text-slate-500">{{ preg_replace('#^https?://#', '', $url) }}</span>
                        </span>
                    </a>
                @endforeach
            </div>
        </x-admin.card>
    </section>
</div>
