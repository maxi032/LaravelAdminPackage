<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Maxi032\LaravelAdminPackage\Models\Category;
use Maxi032\LaravelAdminPackage\Models\CategoryTranslation;
use Maxi032\LaravelAdminPackage\Models\CategoryType;

class CategoryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sortOrder=0;
        $downloads = Category::firstOrCreate([
            'parent_id' => null,
            'type_id'   => CategoryType::where('type', 'downloads')->first()->id,
            'sort_order'   => $sortOrder,
            'status'    => false,
        ], [
            'created_at' => Carbon::now(),
        ]);

        $blog_articles = Category::firstOrCreate([
            'parent_id' => null,
            'type_id'   => CategoryType::where('type', 'blog_articles')->first()->id,
            'sort_order' => ++$sortOrder,
            'status'    => false,
        ], [
            'created_at' => Carbon::now(),
        ]);

        $faq = Category::firstOrCreate([
            'parent_id' => null,
            'type_id'   => CategoryType::where('type', 'faq')->first()->id,
            'sort_order' => ++$sortOrder,
            'status'    => false,
        ], [
            'created_at' => Carbon::now(),
        ]);

        $products = Category::firstOrCreate([
            'parent_id' => null,
            'type_id'   => CategoryType::where('type', 'products')->first()->id,
            'sort_order' => ++$sortOrder,
            'status'    => false,
        ], [
            'created_at' => Carbon::now(),
        ]);

        // insert data in categories_translations too
        if ($downloads) {
            $downloadTranslations = [];
            foreach (config('laravel-admin-package.allowed_languages') as $_language) {
                switch ($_language['code']) {
                    case 'en':
                        $downloadTranslations[$_language['code']]['title'] = 'Internet';
                        $downloadTranslations[$_language['code']]['excerpt'] = 'Here you can find a large variety of internet applications such as Internet browsers, communication tools, servers, etc.';
                        $downloadTranslations[$_language['code']]['content'] = 'Our website gives you a selection of software applications that you can use to navigate, read your email or listen to your favorite podcasts.';
                        CategoryTranslation::firstOrCreate([
                            'language'    => $_language['code'],
                            'category_id' => $downloads->id,
                            'title'       => $downloadTranslations[$_language['code']]['title'],
                            'slug'        => Str::slug($downloadTranslations[$_language['code']]['title']),
                            'content'     => $downloadTranslations[$_language['code']]['content'],
                            'created_at'  => Carbon::now(),
                        ]);
                        break;

                    case 'nl':
                        $downloadTranslations[$_language['code']]['title'] = 'Internet';
                        $downloadTranslations[$_language['code']]['excerpt'] = 'Hier vindt u een grote verscheidenheid aan internettoepassingen zoals internetbrowsers, communicatietools, servers, enz.';
                        $downloadTranslations[$_language['code']]['content'] = 'Onze website biedt u een selectie van softwaretoepassingen die u kunt gebruiken om te navigeren, uw e-mail te lezen of naar uw favoriete podcasts te luisteren.';
                        CategoryTranslation::firstOrCreate([
                            'language'    => $_language['code'],
                            'category_id' => $downloads->id,
                            'title'       => $downloadTranslations[$_language['code']]['title'],
                            'slug'        => Str::slug($downloadTranslations[$_language['code']]['title']),
                            'content'     => $downloadTranslations[$_language['code']]['content'],
                            'created_at'  => Carbon::now(),
                        ]);
                        break;

                    case 'ro':
                        $downloadTranslations[$_language['code']]['title'] = 'Internet';
                        $downloadTranslations[$_language['code']]['excerpt'] = 'Aici gasiti o varietate larga de aplicatii cu care va puteti conecta la Internet, cum ar fi browsere, utilitare pentru comunicare, servere, samd.';
                        $downloadTranslations[$_language['code']]['content'] = 'Site-ul nostru va ofera o selectie de aplicatii software pe care le puteti folosi pentru a naviga, pentru a va citi corespondenta sau pentru a asculta podcasturile favorite.';
                        CategoryTranslation::firstOrCreate([
                            'language'    => $_language['code'],
                            'category_id' => $downloads->id,
                            'title'       => $downloadTranslations[$_language['code']]['title'],
                            'slug'        => Str::slug($downloadTranslations[$_language['code']]['title']),
                            'content'     => $downloadTranslations[$_language['code']]['content'],
                            'created_at'  => Carbon::now(),
                        ]);
                        break;
                }
            }
        }

        if ($blog_articles) {
            $blog_articlesTranslations = [];
            foreach (config('laravel-admin-package.allowed_languages') as $_language) {
                switch ($_language['code']) {
                    case 'en':
                        $blog_articlesTranslations[$_language['code']]['title'] = 'Blog';
                        $blog_articlesTranslations[$_language['code']]['excerpt'] = 'Here you can read the latest articles from our blog.';
                        $blog_articlesTranslations[$_language['code']]['content'] = 'We cover the most interesting topics from news, hobby, science or lifestyle.';
                        CategoryTranslation::firstOrCreate([
                            'language'    => $_language['code'],
                            'category_id' => $blog_articles->id,
                            'title'       => $blog_articlesTranslations[$_language['code']]['title'],
                            'slug'        => Str::slug($blog_articlesTranslations[$_language['code']]['title']),
                            'excerpt'     => $blog_articlesTranslations[$_language['code']]['excerpt'],
                            'content'     => $blog_articlesTranslations[$_language['code']]['content'],
                            'created_at'  => Carbon::now(),
                        ]);
                        break;

                    case 'nl':
                        $blog_articlesTranslations[$_language['code']]['title'] = 'Blog';
                        $blog_articlesTranslations[$_language['code']]['excerpt'] = 'Hier lees je de laatste artikelen van onze blog.';
                        $blog_articlesTranslations[$_language['code']]['content'] = 'We behandelen de meest interessante onderwerpen uit het nieuws, hobby, wetenschap of lifestyle.';
                        CategoryTranslation::firstOrCreate([
                            'language'    => $_language['code'],
                            'category_id' => $blog_articles->id,
                            'title'       => $blog_articlesTranslations[$_language['code']]['title'],
                            'slug'        => Str::slug($blog_articlesTranslations[$_language['code']]['title']),
                            'excerpt'     => $blog_articlesTranslations[$_language['code']]['excerpt'],
                            'content'     => $blog_articlesTranslations[$_language['code']]['content'],
                            'created_at'  => Carbon::now(),
                        ]);
                        break;

                    case 'ro':
                        $blog_articlesTranslations[$_language['code']]['title'] = 'Blog';
                        $blog_articlesTranslations[$_language['code']]['excerpt'] = 'Articole din blogul nostru';
                        $blog_articlesTranslations[$_language['code']]['content'] = 'Avem o gama larga de subiecte de la stiri, hobby, stiinta sau lifestyle.';
                        CategoryTranslation::firstOrCreate([
                            'language'    => $_language['code'],
                            'category_id' => $blog_articles->id,
                            'title'       => $blog_articlesTranslations[$_language['code']]['title'],
                            'slug'        => Str::slug($blog_articlesTranslations[$_language['code']]['title']),
                            'excerpt'     => $blog_articlesTranslations[$_language['code']]['excerpt'],
                            'content'     => $blog_articlesTranslations[$_language['code']]['content'],
                            'created_at'  => Carbon::now(),
                        ]);
                        break;
                }
            }
        }

        // insert data in categories_translations too
        if ($products) {
            $productsTranslations = [];
            foreach (config('laravel-admin-package.allowed_languages') as $_language) {
                switch ($_language['code']) {
                    case 'en':
                        $productsTranslations[$_language['code']]['title'] = 'Products';
                        $productsTranslations[$_language['code']]['content'] = 'Here is an overview of our products';
                        CategoryTranslation::firstOrCreate([
                            'language'    => $_language['code'],
                            'category_id' => $products->id,
                            'title'       => $productsTranslations[$_language['code']]['title'],
                            'excerpt'     => $productsTranslations[$_language['code']]['excerpt'] ?? null,
                            'slug'        => Str::slug($productsTranslations[$_language['code']]['title']),
                            'content'     => $productsTranslations[$_language['code']]['content'],
                            'created_at'  => Carbon::now(),
                        ]);
                        break;

                    case 'nl':
                        $productsTranslations[$_language['code']]['title'] = 'Producten';
                        $productsTranslations[$_language['code']]['content'] = 'Hier kunt u vinden een overzicht van onze producten.';
                        CategoryTranslation::firstOrCreate([
                            'language'    => $_language['code'],
                            'category_id' => $products->id,
                            'title'       => $productsTranslations[$_language['code']]['title'],
                            'excerpt'     => $productsTranslations[$_language['code']]['excerpt'] ?? null,
                            'slug'        => Str::slug($productsTranslations[$_language['code']]['title']),
                            'content'     => $productsTranslations[$_language['code']]['content'],
                            'created_at'  => Carbon::now(),
                        ]);
                        break;

                    case 'ro':
                        $productsTranslations[$_language['code']]['title'] = 'Produse';
                        $productsTranslations[$_language['code']]['content'] = 'O parte din produsele noastre.';
                        CategoryTranslation::firstOrCreate([
                            'language'    => $_language['code'],
                            'category_id' => $products->id,
                            'title'       => $productsTranslations[$_language['code']]['title'],
                            'excerpt'     => $productsTranslations[$_language['code']]['excerpt'] ?? null,
                            'slug'        => Str::slug($productsTranslations[$_language['code']]['title']),
                            'content'     => $productsTranslations[$_language['code']]['content'],
                            'created_at'  => Carbon::now(),
                        ]);
                        break;
                }
            }
        }

        if ($faq) {
            $faqTranslations = [];
            foreach (config('laravel-admin-package.allowed_languages') as $_language) {
                switch ($_language['code']) {
                    case 'en':
                        $faqTranslations[$_language['code']]['title'] = 'FAQ';
                        $faqTranslations[$_language['code']]['content'] = 'If you have questions, here is the place to look for answers.';
                        CategoryTranslation::firstOrCreate([
                            'language'    => $_language['code'],
                            'category_id' => $faq->id,
                            'title'       => $faqTranslations[$_language['code']]['title'],
                            'excerpt'     => $faqTranslations[$_language['code']]['excerpt'] ?? null,
                            'slug'        => Str::slug($faqTranslations[$_language['code']]['title']),
                            'content'     => $faqTranslations[$_language['code']]['content'],
                            'created_at'  => Carbon::now(),
                        ]);
                        break;

                    case 'nl':
                        $faqTranslations[$_language['code']]['title'] = 'FAQ';
                        $faqTranslations[$_language['code']]['content'] = 'Als je vragen hebt, dit is de plek om naar antwoorden te zoeken.';
                        CategoryTranslation::firstOrCreate([
                            'language'    => $_language['code'],
                            'category_id' => $faq->id,
                            'title'       => $faqTranslations[$_language['code']]['title'],
                            'excerpt'     => $faqTranslations[$_language['code']]['excerpt'] ?? null,
                            'slug'        => Str::slug($faqTranslations[$_language['code']]['title']),
                            'content'     => $faqTranslations[$_language['code']]['content'],
                            'created_at'  => Carbon::now(),
                        ]);
                        break;

                    case 'ro':
                        $faqTranslations[$_language['code']]['title'] = 'Intrebari frecvente';
                        $faqTranslations[$_language['code']]['content'] = 'Daca aveti intrebari, puteti consulta sectiunea noastra de intrebari frecvente.';
                        CategoryTranslation::firstOrCreate([
                            'language'    => $_language['code'],
                            'category_id' => $faq->id,
                            'title'       => $faqTranslations[$_language['code']]['title'],
                            'excerpt'     => $faqTranslations[$_language['code']]['excerpt'] ?? null,
                            'slug'        => Str::slug($faqTranslations[$_language['code']]['title']),
                            'content'     => $faqTranslations[$_language['code']]['content'],
                            'created_at'  => Carbon::now(),
                        ]);
                        break;
                }
            }
        }
    }
}
