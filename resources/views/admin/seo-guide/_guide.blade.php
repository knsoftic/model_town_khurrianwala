{{--
    SEO guide content in Roman Urdu, shared by the admin page and the private link
    ($checks, $pages from App\Services\SeoGuide). Admin menu and button names stay in English,
    exactly as they appear on screen.
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
        'Har page par title, meta description aur keywords — page ke SEO box se, warna website ke default se',
        'Robots meta tag aur X-Robots-Tag header: '.app(\App\Services\SeoService::class)->defaultRobots(),
        'Har page par canonical link, jo usi page ki taraf ishara karta hai',
        'Author aur publisher tags',
        'Open Graph aur Twitter tags — WhatsApp ya Facebook par link share karne par title, text aur photo nazar aati hai',
        'Local search tags (geo.region, geo.position) aur society ka map pin',
        'Google ke liye structured data: business (RealEstateAgent), WebSite, breadcrumbs, plots (Product), FAQ aur location (Place)',
        'XML sitemap, jo plots ya pages badalne par khud update hota hai',
        'robots.txt (sitemap link ke saath), aur AI search ke liye llms.txt',
        'Images par alt text, lazy loading aur chhoti WebP images',
        'Mobile-friendly pages, aur tez loading ke liye inline CSS',
        'Admin panel search engines se chhupa hua hai',
    ];

    $onPage = [
        [
            'title' => 'Website ka title, description aur keywords',
            'text' => 'Settings → SEO & Scripts kholein. Homepage ke liye Google yehi dikhata hai, aur baaqi pages ke liye bhi yehi default hai.',
            'points' => [
                'Title: 50–60 characters, main keyword shuru mein — maslan “Model Town Khurrianwala – Plots on Installments Faisalabad”.',
                'Description: 140–160 characters. Batayein aap kya de rahe hain, kahan, aur phone number zaroor likhein.',
                'Keywords: woh phrase likhein jo buyer Google par search karta hai, phir Add dabayein — maslan “5 marla plot for sale in Faisalabad”. Hatane ke liye × dabayein.',
            ],
            'links' => [['SEO settings', $seoSettings, true]],
        ],
        [
            'title' => 'Har page ka apna title aur description',
            'text' => 'Website Content → Pages → page ke saath Edit → “SEO” box → Edit SEO.',
            'points' => [
                'Har page ka title aur description alag hona chahiye. Title ke aakhir mein “| Model Town Khurrianwala” likhein.',
                'Sirf usi page ke 3–5 keywords add karein (maslan Installment Plans ke liye: “plots on installments in Faisalabad”).',
                '“Canonical URL” khaali chhorein aur “Robots” Default par rakhein. noindex sirf us page ke liye chunein jo Google par nahi dikhana.',
            ],
            'links' => [['Pages', route('admin.pages.index'), true]],
        ],
        [
            'title' => 'Plot pages',
            'text' => 'Plots → All Plot Sizes → Edit → SEO box. Har plot size ka Google mein apna alag page hai.',
            'points' => [
                'Title is tarah likhein: “5 Marla Plot for Sale in Faisalabad”. Description mein price, down payment aur monthly installment likhein.',
                'Plots ki asli photos upload karein; plot ka naam hi un photos ka alt text ban jata hai.',
            ],
            'links' => [['All Plot Sizes', route('admin.plots.index'), true]],
        ],
        [
            'title' => 'Location page',
            'text' => 'Website Content → Locations → Edit. Address aur map pin bilkul wohi rakhein jo Google Business Profile par hai (Off-page SEO ka step 1).',
            'links' => [['Locations', route('admin.locations.index'), true]],
        ],
        [
            'title' => 'Headings aur page ka text',
            'text' => 'Google page par likhe hue alfaaz parhta hai. Jin pages par kaam ka text hota hai, woh sirf tasveeron wale pages se upar aate hain.',
            'points' => [
                'Har page ki aik main heading hoti hai (page heading). Main keyword us mein rakhein.',
                'About aur Plots pages par kam az kam 300 alfaaz likhein: location, sahuliyaat, plot sizes, payment plan aur possession.',
                'Keywords qudrati andaaz mein likhein. Aik hi phrase baar baar dohrane se ranking kharab hoti hai.',
            ],
            'links' => [['Homepage Sections', route('admin.sections.index'), true], ['Pages', route('admin.pages.index'), true]],
        ],
        [
            'title' => 'Photos',
            'text' => 'Website Content → Gallery. Photo ka title hi us ka alt text banta hai, jise Google Images parhta hai.',
            'points' => [
                'Har photo ko wazeh title dein, maslan “Model Town Khurrianwala ka main gate, Jhumra Road”.',
                'Upload se pehle file ka naam rakhein (model-town-khurrianwala-main-gate.jpg) aur size 500 KB se kam rakhein.',
            ],
            'links' => [['Gallery', route('admin.gallery.index'), true]],
        ],
        [
            'title' => 'Videos',
            'text' => 'Website Content → Videos. Har video ka title aur description keyword ke saath likhein. YouTube par lagi videos YouTube search se bhi visitors laati hain.',
            'links' => [['Videos', route('admin.videos.index'), true]],
        ],
        [
            'title' => 'FAQs (aksar poochay jane wale sawal)',
            'text' => 'Website Content → FAQs. Woh sawal add karein jo buyers phone par poochte hain — Google inhein aap ke result ke neeche bhi dikha sakta hai.',
            'points' => ['Maslan: “5 marla plot ki qeemat kya hai?”, “Kya booking fee hai?”, “Possession kab milega?”'],
            'links' => [['FAQs', route('admin.faqs.index'), true]],
        ],
        [
            'title' => 'Pages ke darmiyan links',
            'text' => 'Website Content → Menus. Plots, Installment Plans, Location aur Contact ko header aur footer menu mein rakhein taake Google har page dhoond sake.',
            'links' => [['Menus', route('admin.menus.index'), true]],
        ],
        [
            'title' => 'Mazeed keywords dhoondein',
            'text' => 'Dekhein log asal mein kya search karte hain, phir woh phrases website keywords aur page ke text mein add karein.',
            'points' => [
                'Google mein “plots in Faisalabad” likhein aur neeche aane wali suggestions aur “People also ask” box parhein.',
                'Search Console → Performance → Queries batata hai aap kin searches mein aa rahe hain (Technical SEO step 2 ke baad).',
                'Google Trends (Pakistan) mein phrases ka muqabla karein, maslan “plot on installment” aur “plots for sale”.',
            ],
            'links' => [['Google Trends — Pakistan', 'https://trends.google.com/trends/explore?geo=PK&q=plots%20in%20faisalabad', false], ['Keyword Planner', 'https://ads.google.com/home/tools/keyword-planner/', false]],
        ],
        [
            'title' => 'Apna kaam check karein',
            'text' => 'Koi bhi page kholein, right-click → View page source, aur title, description, keywords aur robots tags dekhein. Ya neeche wala “Page-by-page check” dekhein — Save karte hi update ho jata hai.',
            'links' => [['Website kholein', $home, false]],
        ],
    ];

    $technical = [
        [
            'title' => 'Secure address (https) aur website ka aik hi address',
            'text' => 'aaPanel → Website → '.$host.' → SSL → Let’s Encrypt → Apply, phir Force HTTPS ON karein. Aik address chunein (www ke saath ya baghair) aur doosre ko us par redirect karein (aaPanel → Redirect, 301).',
            'points' => ['Server ki .env file mein neeche wali line likhein, phir server command dobara chalayein.'],
            'code' => 'APP_URL=https://'.$host,
        ],
        [
            'title' => 'Google Search Console connect karein',
            'text' => 'Search Console → Add property → “URL prefix” → https://'.$host.' → “HTML tag” chunein → tag copy karein. Usay Settings → Verification & Analytics → Google Search Console mein paste karke Save karein. Phir wapas Search Console mein Verify dabayein.',
            'links' => [['Google Search Console', 'https://search.google.com/search-console', false], ['Verification settings', $verification, true]],
        ],
        [
            'title' => 'Sitemap submit karein',
            'text' => 'Search Console → Sitemaps → neeche wali line likhein → Submit. Aik din baad status “Success” hona chahiye.',
            'code' => 'sitemap.xml',
            'links' => [['Sitemap kholein', $sitemap, false]],
        ],
        [
            'title' => 'Main pages ko index karwayein',
            'text' => 'Search Console → URL inspection (upar wala search box) → page ka address paste karein → Request indexing. Homepage, Plots, Installment Plans, har plot size, Location aur Contact ke liye yeh karein — aur jab bhi koi page badlein, dobara karein.',
        ],
        [
            'title' => 'Bing connect karein (Yahoo aur DuckDuckGo bhi isi se)',
            'text' => 'Bing Webmaster Tools → sign in → “Import from Google Search Console” (sab se aasaan). Ya site add karke “HTML Meta Tag” chunein aur usay Settings → Verification & Analytics → Bing mein paste karein. Wahan bhi sitemap submit karein.',
            'links' => [['Bing Webmaster Tools', 'https://www.bing.com/webmasters', false], ['Verification settings', $verification, true]],
        ],
        [
            'title' => 'Google Analytics connect karein',
            'text' => 'Google Analytics → Admin → Create property → Web → '.$host.' → Measurement ID (G-XXXXXXXXXX) copy karein. Usay Settings → Verification & Analytics → Google Analytics mein paste karke Save karein. Analytics mein Admin → Product links se Search Console bhi jor dein.',
            'links' => [['Google Analytics', 'https://analytics.google.com/', false], ['Verification settings', $verification, true]],
        ],
        [
            'title' => 'Speed check karein',
            'text' => 'PageSpeed Insights → homepage aur aik plot page Mobile par test karein. 90 ya us se zyada ka target rakhein.',
            'points' => [
                'Photos upload se pehle compress karein; “Load analytics after the first scroll or tap” ON rakhein.',
                'Har update ke baad server command chalayein — is mein speed script aur image optimisation dono shamil hain.',
            ],
            'links' => [['PageSpeed Insights', 'https://pagespeed.web.dev/', false]],
        ],
        [
            'title' => 'Structured data test karein',
            'text' => 'Rich Results Test → homepage, FAQ page aur aik plot page test karein. Errors theek karna zaroori hai; warnings se koi masla nahi.',
            'links' => [['Rich Results Test', 'https://search.google.com/test/rich-results', false], ['Schema validator', 'https://validator.schema.org/', false]],
        ],
        [
            'title' => 'robots.txt khula rakhein',
            'text' => 'robots.txt mein “Disallow:” ke aage kuch nahi hona chahiye (yaani sab allowed hai) aur Sitemap wali line honi chahiye. Settings → SEO mein “Show this website in search engines” hamesha ON rakhein.',
            'links' => [['robots.txt kholein', $robotsTxt, false], ['SEO settings', $seoSettings, true]],
        ],
        [
            'title' => 'Search Console mein har hafte check',
            'text' => 'Pages (koi page index kyun nahi hua), Core Web Vitals, HTTPS, aur Security & Manual actions dekhein (yeh dono khaali hone chahiyein). Agar koi page delete ya rename karein to redirect lagayein taake purane links “page not found” par na jayein.',
        ],
        [
            'title' => 'Dekhein Google ne kya index kiya hai',
            'text' => 'Google par neeche wali line search karein. Har zaroori page list mein hona chahiye.',
            'code' => 'site:'.$host,
            'links' => [['Google par search karein', 'https://www.google.com/search?q='.urlencode('site:'.$host), false]],
        ],
    ];

    $offPage = [
        [
            'title' => 'Google Business Profile — sab se zaroori step',
            'text' => 'Is se society Google Maps par aati hai, aur “housing society near me” ya “plots in Faisalabad” jaisi searches ke map box mein nazar aati hai.',
            'points' => [
                'business.google.com → Add business → “Model Town Khurrianwala” → category maslan Housing society ya Real estate developer.',
                'Bilkul sahi address aur map pin (Main Bazar Khurrianwala, Jhumra Road), phone '.$phone.', website https://'.$host.'.',
                'Verify karein (postcard, phone ya video), phir 20 se zyada asli photos, office timings aur plot sizes (products ki tarah) add karein.',
                'Har hafte aik update ya offer post karein.',
            ],
            'links' => [['Google Business Profile', 'https://business.google.com/', false]],
        ],
        [
            'title' => 'Google reviews',
            'text' => 'Har buyer aur site visit karne wale se review ki darkhwast karein — Business Profile se review link bhejein. Har review ka jawab dein, achha ho ya bura. Kabhi fake reviews na kharidein.',
        ],
        [
            'title' => 'Har jagah wohi naam, address aur phone',
            'text' => 'Business ka naam, address aur phone number har listing aur profile par bilkul waisa hi likhein jaisa website ke footer mein hai. Jo details har jagah match karein, Google un par bharosa karta hai.',
        ],
        [
            'title' => 'Property portals',
            'text' => 'Har plot size Zameen.com, Graana.com aur OLX par asli photos, price aur installment plan ke saath list karein, aur description mein website ka link zaroor dein.',
            'links' => [['Zameen.com', 'https://www.zameen.com/', false], ['Graana.com', 'https://www.graana.com/', false], ['OLX', 'https://www.olx.com.pk/', false]],
        ],
        [
            'title' => 'Social media profiles',
            'text' => 'Facebook page, Instagram, YouTube aur TikTok: bio mein website ka link dein, har hafte site ki progress post karein, aur har profile Settings → Social Links mein add karein taake Google unhein website se jor sake.',
            'links' => [['Social Links', route('admin.social-links.index'), true]],
        ],
        [
            'title' => 'YouTube videos',
            'text' => 'Site tour, plot visits aur development updates ki videos upload karein.',
            'points' => [
                'Title is tarah: “Model Town Khurrianwala Faisalabad – 5 Marla Plot on Installments | Site Tour”.',
                'Description ki pehli line mein website ka link dein; phir woh video website ke Videos mein bhi add karein.',
            ],
        ],
        [
            'title' => 'Mazeed map aur business listings',
            'text' => 'Society ko Bing Places, Apple Business Connect aur bharose ke qabil Pakistani business directories mein bhi add karein — wohi details ke saath.',
            'links' => [['Bing Places', 'https://www.bingplaces.com/', false], ['Apple Business Connect', 'https://businessconnect.apple.com/', false]],
        ],
        [
            'title' => 'Doosri websites se links (backlinks)',
            'text' => 'Faisalabad ki news websites, property blogs aur aap ke plots bechne wale dealers se apni website ka link lagwayein — maslan kisi development milestone ki khabar ke saath.',
            'points' => ['Aik asli local website ka link sainkron spam links se behtar hai. Kabhi link packages na kharidein — Google is wajah se website ko neeche kar sakta hai.'],
        ],
        [
            'title' => 'WhatsApp aur Facebook groups',
            'text' => 'Faisalabad ke property groups mein page links (Plots, Installment Plans) aur apna short link share karein, taake log sirf phone number par nahi balke website par aayein.',
            'links' => [['Short link settings', route('admin.settings.edit', 'redirect'), true]],
        ],
        [
            'title' => 'Nataij (results) dekhein',
            'text' => 'Search Console → Performance mein searches, clicks aur position; Links mein yeh ke kaun aap ko link kar raha hai. Business Profile → Performance mein calls aur direction requests.',
            'links' => [['Google Search Console', 'https://search.google.com/search-console', false]],
        ],
    ];

    $routine = [
        'Har hafte' => ['Google Business Profile aur social media par post karein (development kaam ki photos).', 'Naye Google reviews ka jawab dein.', 'Search Console: Performance dekhein aur Pages mein naye errors theek karein.'],
        'Har mahine' => ['Yeh guide kholein aur jo bhi laal (red) hai woh theek karein.', '1–2 naye FAQs ya pages par mazeed text add karein.', 'PageSpeed Insights par speed test karein.', 'Prices aur plans website aur property portals par aik saath update karein.'],
        'Website update ke baad' => ['Server command chalayein.', 'Jo pages badle hain un ke liye Request indexing karein.'],
    ];
@endphp

<nav class="scrollbar-none -mx-4 mb-6 flex gap-2 overflow-x-auto px-4 lg:mx-0 lg:px-0" aria-label="Guide ke hisse">
    @foreach (['status' => 'SEO Status', 'on-page' => 'On-page SEO', 'pages' => 'Page-by-page check', 'technical' => 'Technical SEO', 'off-page' => 'Off-page SEO', 'routine' => 'Routine', 'links' => 'Kaam ke links'] as $anchor => $label)
        <a href="#{{ $anchor }}" class="shrink-0 rounded-full border border-slate-200 bg-white px-3.5 py-1.5 text-sm font-medium text-slate-700 shadow-xs transition hover:border-slate-300 hover:text-slate-900">{{ $label }}</a>
    @endforeach
</nav>

<div class="space-y-6">
    <section id="status" class="scroll-mt-24">
        <div class="grid gap-6 xl:grid-cols-2">
            <x-admin.card title="Aap ki SEO checklist" :description="count($checks).' mein se '.$done.' mukammal. Laal (red) items par aap ka kaam baaqi hai — har aik ke saath sahi jagah ka button hai.'" :padding="false">
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

            <x-admin.card title="Website yeh sab khud kar rahi hai" description="Yeh on-page SEO har page par khud-ba-khud kaam karta hai — is ke liye aap ko kuch nahi karna.">
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
        <x-admin.card title="On-page SEO — step by step" description="Aap ke apne pages par jo kuch hai: titles, descriptions, keywords, text aur photos. Lock wale buttons admin panel kholte hain (pehle sign in karein).">
            @include('admin.seo-guide._steps', ['steps' => $onPage])
        </x-admin.card>
    </section>

    <section id="pages" class="scroll-mt-24">
        <x-admin.card title="Page-by-page check" description="Har page ka woh title aur description jo is waqt Google ko mil raha hai. Title: 30–65 characters. Description: 110–165 characters." :padding="false">
            <div class="overflow-x-auto">
                <table class="a-table">
                    <thead>
                        <tr>
                            <th>Page</th>
                            <th>Google mein title aur description</th>
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
                                    {{ $row['keywords'] }} <span class="text-slate-400">{{ $row['own_keywords'] ? 'apne' : 'website ke' }}</span>
                                </td>
                                <td>
                                    @if ($row['ok'])
                                        <span class="a-badge bg-emerald-50 text-emerald-700 ring-emerald-600/20">Theek hai</span>
                                    @else
                                        <span class="a-badge bg-amber-50 text-amber-800 ring-amber-600/20">Check karein</span>
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
        <x-admin.card title="Technical SEO — aap ka flow" description="Yeh kaam aik dafa, isi tarteeb se karein. In se Google website ko dhoond sakta hai, us par bharosa karta hai, aur aap visitors gin sakte hain.">
            @include('admin.seo-guide._steps', ['steps' => $technical])
        </x-admin.card>
    </section>

    <section id="off-page" class="scroll-mt-24">
        <x-admin.card title="Off-page SEO" description="Website se bahar ka kaam jo Google ka bharosa barhata hai: maps, reviews, listings, social media aur doosri websites ke links.">
            @include('admin.seo-guide._steps', ['steps' => $offPage])
        </x-admin.card>
    </section>

    <section id="routine" class="scroll-mt-24">
        <x-admin.card title="Routine" description="SEO tab kaam karta hai jab thora thora, baqaidagi se kiya jaye.">
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
        <x-admin.card title="Kaam ke links">
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
