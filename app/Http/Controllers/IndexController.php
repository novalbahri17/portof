<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\Certification;
use App\Models\Experience;
use App\Models\Project;
use App\Models\SiteSetting;
use Inertia\Inertia;

class IndexController extends Controller
{
    public function __invoke()
    {
        $socialKeys = ['github', 'linkedin', 'twitter', 'instagram', 'youtube', 'tiktok', 'discord', 'website'];
        $socials = [];
        foreach ($socialKeys as $key) {
            $url = SiteSetting::get("social_{$key}", '');
            if ($url) {
                $socials[] = ['network' => $key, 'url' => $url];
            }
        }

        $certifications = Certification::published()->orderBy('sort_order')->get();

        $sectionVisibility = [
            'about' => SiteSetting::get('section_about_visible', '1') === '1',
            'projects' => SiteSetting::get('section_projects_visible', '1') === '1',
            'portfolio' => SiteSetting::get('section_portfolio_visible', '1') === '1',
            'certifications' => SiteSetting::get('section_certifications_visible', '1') === '1' && $certifications->isNotEmpty(),
            'experience' => SiteSetting::get('section_experience_visible', '1') === '1',
            'blog' => SiteSetting::get('section_blog_visible', '1') === '1',
            'contact' => SiteSetting::get('section_contact_visible', '1') === '1',
        ];

        return Inertia::render('Index', [
            'heroTitle' => SiteSetting::get('hero_title', 'Halo, saya Seorang Developer Laravel'),
            'heroSubtitle' => SiteSetting::get('hero_subtitle', 'Membangun pengalaman web dengan kode yang bersih dan desain yang fungsional.'),
            'heroBadge' => SiteSetting::get('hero_badge', 'Full Stack Developer'),
            'heroImage' => SiteSetting::get('hero_image', ''),
            'heroImageShape' => SiteSetting::get('hero_image_shape', 'circle'),
            'heroImageSize' => (int) SiteSetting::get('hero_image_size', '112'),
            'about' => SiteSetting::get('about', ''),
            'hobbies' => SiteSetting::get('hobbies', ''),
            'socials' => $socials,
            'contactInfo' => [
                'email' => SiteSetting::get('contact_email', ''),
                'phone' => SiteSetting::get('contact_phone', ''),
                'address' => SiteSetting::get('contact_address', ''),
            ],
            'sideProjects' => Project::published()->sideProjects()->orderBy('sort_order')->paginate(3, ['*'], 'side_projects_page'),
            'portfolios' => Project::published()->portfolio()->orderBy('sort_order')->paginate(3, ['*'], 'portfolios_page'),
            'certifications' => $certifications,
            'experiences' => Experience::published()->orderBy('start_date', 'desc')->get(['id', 'type', 'title', 'institution', 'location', 'start_date', 'end_date', 'is_current', 'description']),
            'blogs' => Blog::published()->latest('published_at')->paginate(3, ['*'], 'blogs_page'),
            'sectionVisibility' => $sectionVisibility,
            'seo' => [
                'title' => SiteSetting::get('seo_title', ''),
                'description' => SiteSetting::get('seo_description', ''),
                'keywords' => SiteSetting::get('seo_keywords', ''),
                'canonical' => SiteSetting::get('seo_canonical', ''),
                'ogTitle' => SiteSetting::get('og_title', ''),
                'ogDescription' => SiteSetting::get('og_description', ''),
                'ogImage' => SiteSetting::get('og_image', ''),
                'ogType' => SiteSetting::get('og_type', 'website'),
                'twitterCard' => SiteSetting::get('twitter_card', 'summary_large_image'),
                'twitterTitle' => SiteSetting::get('twitter_title', ''),
                'twitterDescription' => SiteSetting::get('twitter_description', ''),
                'twitterImage' => SiteSetting::get('twitter_image', ''),
                'favicon' => SiteSetting::get('favicon', ''),
            ],
        ]);
    }
}
