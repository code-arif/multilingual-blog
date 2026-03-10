<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\PostTranslation;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@multilingualblog.com',
            'password' => Hash::make('Admin@12345'),
            'role' => 'admin',
            'is_active' => true,
            'email_verified_at' => now(),
            'bio' => 'Platform administrator with full access to all features.',
        ]);

        // Create sample visitor
        User::create([
            'name' => 'John Doe',
            'email' => 'user@multilingualblog.com',
            'password' => Hash::make('User@12345'),
            'role' => 'visitor',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Create categories
        $categories = [
            ['name' => 'Technology', 'slug' => 'technology', 'color' => '#6366f1', 'icon' => 'cpu', 'status' => true],
            ['name' => 'Travel', 'slug' => 'travel', 'color' => '#10b981', 'icon' => 'map', 'status' => true],
            ['name' => 'Food', 'slug' => 'food', 'color' => '#f59e0b', 'icon' => 'utensils', 'status' => true],
            ['name' => 'Science', 'slug' => 'science', 'color' => '#3b82f6', 'icon' => 'flask', 'status' => true],
            ['name' => 'Culture', 'slug' => 'culture', 'color' => '#ec4899', 'icon' => 'landmark', 'status' => true],
        ];

        foreach ($categories as $cat) {
            Category::create($cat);
        }

        // Create sample posts with multilingual content
        $posts = [
            [
                'category_id' => 1,
                'en' => [
                    'title' => 'Getting Started with Laravel 10',
                    'content' => '<h2>Introduction</h2><p>Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling.</p><p>Laravel takes the pain out of development by easing common tasks used in many web projects, such as authentication, routing, sessions, and caching.</p><h2>Installation</h2><p>Before installing Laravel, you should ensure that your local machine has PHP 8.1, Composer, and the following PHP extensions...</p>',
                    'excerpt' => 'A comprehensive guide to getting started with Laravel 10, the latest version of the popular PHP framework.',
                ],
                'bn' => [
                    'title' => 'Laravel 10 দিয়ে শুরু করুন',
                    'content' => '<h2>পরিচিতি</h2><p>Laravel হল একটি ওয়েব অ্যাপ্লিকেশন ফ্রেমওয়ার্ক যা একটি অভিব্যক্তিমূলক এবং মার্জিত সিনট্যাক্স সহ। আমরা বিশ্বাস করি যে উন্নয়ন একটি উপভোগযোগ্য এবং সৃজনশীল অভিজ্ঞতা হওয়া উচিত।</p><p>Laravel অনেক ওয়েব প্রকল্পে ব্যবহৃত সাধারণ কাজগুলিকে সহজ করে তুলে উন্নয়নের ব্যথা দূর করে, যেমন প্রমাণীকরণ, রাউটিং, সেশন এবং ক্যাশিং।</p>',
                    'excerpt' => 'Laravel 10 শুরু করার জন্য একটি ব্যাপক গাইড, জনপ্রিয় PHP ফ্রেমওয়ার্কের সর্বশেষ সংস্করণ।',
                ],
                'es' => [
                    'title' => 'Comenzando con Laravel 10',
                    'content' => '<h2>Introducción</h2><p>Laravel es un framework de aplicaciones web con una sintaxis expresiva y elegante. Creemos que el desarrollo debe ser una experiencia agradable y creativa para ser verdaderamente satisfactoria.</p><p>Laravel elimina el dolor del desarrollo facilitando las tareas comunes utilizadas en muchos proyectos web, como la autenticación, el enrutamiento, las sesiones y el caché.</p>',
                    'excerpt' => 'Una guía completa para comenzar con Laravel 10, la última versión del popular framework PHP.',
                ],
            ],
            [
                'category_id' => 2,
                'en' => [
                    'title' => 'Exploring the Streets of Tokyo',
                    'content' => '<h2>Tokyo: A City of Contrasts</h2><p>Tokyo is one of the most fascinating cities in the world, where ancient temples stand beside gleaming skyscrapers and traditional culture blends seamlessly with modern innovation.</p><p>From the bustling Shibuya crossing to the serene Senso-ji temple in Asakusa, Tokyo offers an unparalleled experience for every type of traveler.</p><h2>Getting Around</h2><p>Tokyo\'s extensive rail network makes it incredibly easy to navigate the city. The JR Pass provides unlimited access to most train lines...</p>',
                    'excerpt' => 'Discover the magic of Tokyo, a city where ancient traditions and futuristic technology coexist in perfect harmony.',
                ],
                'bn' => [
                    'title' => 'টোকিওর রাস্তায় অন্বেষণ',
                    'content' => '<h2>টোকিও: বৈপরীত্যের একটি শহর</h2><p>টোকিও বিশ্বের সবচেয়ে আকর্ষণীয় শহরগুলির মধ্যে একটি, যেখানে প্রাচীন মন্দিরগুলি চকচকে আকাশচুম্বী ভবনের পাশে দাঁড়িয়ে আছে এবং ঐতিহ্যবাহী সংস্কৃতি আধুনিক উদ্ভাবনের সাথে নিরবচ্ছিন্নভাবে মিশ্রিত হয়।</p>',
                    'excerpt' => 'টোকিওর জাদু আবিষ্কার করুন, একটি শহর যেখানে প্রাচীন ঐতিহ্য এবং ভবিষ্যতের প্রযুক্তি নিখুঁত সামঞ্জস্যে সহাবস্থান করে।',
                ],
                'es' => [
                    'title' => 'Explorando las Calles de Tokio',
                    'content' => '<h2>Tokio: Una Ciudad de Contrastes</h2><p>Tokio es una de las ciudades más fascinantes del mundo, donde los templos antiguos se erigen junto a rascacielos relucientes y la cultura tradicional se mezcla perfectamente con la innovación moderna.</p>',
                    'excerpt' => 'Descubre la magia de Tokio, una ciudad donde las tradiciones antiguas y la tecnología futurista coexisten en perfecta armonía.',
                ],
            ],
            [
                'category_id' => 3,
                'en' => [
                    'title' => 'The Art of Making Perfect Biryani',
                    'content' => '<h2>A Culinary Masterpiece</h2><p>Biryani is more than just a dish; it\'s a culinary tradition that has been perfected over centuries. Originating from the Mughal kitchens of India, this aromatic rice dish has evolved into countless regional variations across South Asia and beyond.</p><p>The key to perfect biryani lies in the quality of ingredients, the technique of cooking, and the layering of flavors...</p>',
                    'excerpt' => 'Master the art of cooking the perfect biryani with this detailed guide covering ingredients, techniques, and regional variations.',
                ],
                'bn' => [
                    'title' => 'নিখুঁত বিরিয়ানি তৈরির শিল্প',
                    'content' => '<h2>একটি রন্ধনসম্পর্কীয় মাস্টারপিস</h2><p>বিরিয়ানি শুধু একটি খাবার নয়; এটি একটি রন্ধনসম্পর্কীয় ঐতিহ্য যা শতাব্দী ধরে পরিপূর্ণ হয়েছে। ভারতের মুঘল রান্নাঘর থেকে উদ্ভূত, এই সুগন্ধি ভাতের খাবারটি সারা দক্ষিণ এশিয়া এবং তার বাইরে অগণিত আঞ্চলিক বৈচিত্র্যে বিকশিত হয়েছে।</p>',
                    'excerpt' => 'এই বিস্তারিত গাইড দিয়ে নিখুঁত বিরিয়ানি রান্নার শিল্প আয়ত্ত করুন।',
                ],
                'es' => [
                    'title' => 'El Arte de Hacer el Biryani Perfecto',
                    'content' => '<h2>Una Obra Maestra Culinaria</h2><p>El biryani es más que un plato; es una tradición culinaria que se ha perfeccionado durante siglos. Originario de las cocinas mogolas de la India, este aromático plato de arroz ha evolucionado en innumerables variaciones regionales en todo el sur de Asia y más allá.</p>',
                    'excerpt' => 'Domina el arte de cocinar el biryani perfecto con esta guía detallada sobre ingredientes, técnicas y variaciones regionales.',
                ],
            ],
        ];

        foreach ($posts as $postData) {
            $categoryId = $postData['category_id'];
            $post = Post::create([
                'category_id' => $categoryId,
                'author_id' => $admin->id,
                'slug' => Str::slug($postData['en']['title']),
                'status' => 'published',
                'published_at' => now()->subDays(rand(1, 30)),
            ]);

            foreach (['en', 'bn', 'es'] as $locale) {
                PostTranslation::create([
                    'post_id' => $post->id,
                    'locale' => $locale,
                    'title' => $postData[$locale]['title'],
                    'content' => $postData[$locale]['content'],
                    'excerpt' => $postData[$locale]['excerpt'],
                    'meta_title' => $postData[$locale]['title'],
                    'meta_description' => $postData[$locale]['excerpt'],
                ]);
            }
        }

        $this->command->info('✅ Database seeded successfully!');
        $this->command->info('Admin: admin@multilingualblog.com / Admin@12345');
        $this->command->info('User: user@multilingualblog.com / User@12345');
    }
}
