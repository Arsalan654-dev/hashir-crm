<?php

namespace Database\Seeders;

use App\Models\CalendarEvent;
use App\Models\Category;
use App\Models\ChatContact;
use App\Models\ChatMessage;
use App\Models\Contact;
use App\Models\Customer;
use App\Models\EmailMessage;
use App\Models\FileManagerItem;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\NotificationItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Todo;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TemplateContentSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Categories
        $electronics = Category::create(['name' => 'Electronic', 'slug' => 'electronic', 'icon' => 'bx bx-desktop', 'sort_order' => 1]);
        $furniture = Category::create(['name' => 'Furniture', 'slug' => 'furniture', 'icon' => 'bx bx-radar', 'sort_order' => 2]);
        $baby = Category::create(['name' => 'Baby & Kids', 'slug' => 'baby-kids', 'icon' => 'bx bx-command', 'sort_order' => 3]);
        $fitness = Category::create(['name' => 'Fitness', 'slug' => 'fitness', 'icon' => 'bx bx-dumbbell', 'sort_order' => 4]);

        foreach ([
            ['Mobile', 'mobile', $electronics], ['Camera', 'camera', $electronics],
            ['Computers', 'computers', $electronics], ['Laptops', 'laptops', $electronics],
            ['Chairs', 'chairs', $furniture], ['Tables', 'tables', $furniture],
            ['Beds', 'beds', $furniture], ['Clothing', 'clothing', $baby],
            ['Toys', 'toys', $baby], ['Gym equipment', 'gym', $fitness],
            ['Yoga mat', 'yoga-mat', $fitness],
        ] as [$name, $slug, $parent]) {
            Category::create(['name' => $name, 'slug' => $slug, 'parent_id' => $parent->id, 'sort_order' => 10]);
        }

        // Products (template-style chairs)
        $products = [
            ['name' => 'Home & Office Chair Crime', 'sku' => 'Fluid HB - Lite - AN White', 'category_id' => $furniture->id, 'color' => 'Gray', 'tags' => 'Chair', 'price' => 260, 'old_price' => 280, 'image' => 'assets/images/product/img-1.png', 'rating' => 4.5, 'stock' => 40, 'badge' => 'New', 'badge_class' => 'bg-primary', 'is_featured' => true, 'description' => 'Material : Steel + PVC Leather + Sponge'],
            ['name' => 'Sofa Home Chair Black', 'sku' => 'Fluid HB - Lite - AN Black', 'category_id' => $furniture->id, 'color' => 'Black', 'tags' => 'Chair', 'price' => 180, 'old_price' => 200, 'image' => 'assets/images/product/img-2.png', 'rating' => 4, 'stock' => 25, 'is_featured' => false, 'description' => 'Comfortable sofa chair for living room'],
            ['name' => 'Tuition Classes Chair Crime', 'sku' => 'Fluid HB - Lite - AN Grey', 'category_id' => $furniture->id, 'color' => 'Gray', 'tags' => 'Chair', 'price' => 150, 'old_price' => 190, 'image' => 'assets/images/product/img-3.png', 'rating' => 4.2, 'stock' => 60, 'badge' => '- 20 %', 'badge_class' => 'bg-danger', 'is_featured' => false, 'description' => '3 Year Warranty and Stock Available'],
            ['name' => 'Home & Office Chair', 'sku' => 'Fluid HB - Lite - AN Grey', 'category_id' => $furniture->id, 'color' => 'Gray', 'tags' => 'Chair', 'price' => 2360, 'old_price' => 3500, 'image' => 'assets/images/product/img-4.png', 'rating' => 4.8, 'stock' => 12, 'is_featured' => true, 'description' => 'Material : Steel + PVC Leather + Sponge'],
            ['name' => 'Dining Table Chair', 'sku' => 'Fluid HB - Lite - AN Dark', 'category_id' => $furniture->id, 'color' => 'Black', 'tags' => 'Chair', 'price' => 210, 'old_price' => 250, 'image' => 'assets/images/product/img-5.png', 'rating' => 3.9, 'stock' => 33, 'is_featured' => false, 'description' => 'Overall Dimention : 16.5" x 19.5" x 24.3"'],
            ['name' => 'Hotel Dining Table And Chair', 'sku' => 'Fluid HB - Lite - AN Dark', 'category_id' => $furniture->id, 'color' => 'Black', 'tags' => 'Table', 'price' => 990, 'old_price' => 1200, 'image' => 'assets/images/product/img-6.png', 'rating' => 4.1, 'stock' => 18, 'is_featured' => true, 'description' => 'Choose From Standard, Express Or Assembled Services'],
            ['name' => 'Office Chair Crime', 'sku' => 'Fluid HB - Lite - AN White', 'category_id' => $furniture->id, 'color' => 'White', 'tags' => 'Chair', 'price' => 320, 'old_price' => 360, 'image' => 'assets/images/product/img-7.png', 'rating' => 4.6, 'stock' => 22, 'is_featured' => false, 'description' => 'Ergonomic office chair with lumbar support'],
            ['name' => 'Tuition Classes Chair', 'sku' => 'Fluid HB - Lite - AN White', 'category_id' => $furniture->id, 'color' => 'White', 'tags' => 'Chair', 'price' => 2360, 'old_price' => 3500, 'image' => 'assets/images/product/img-8.png', 'rating' => 4.7, 'stock' => 9, 'is_featured' => true, 'description' => '3 Year Warranty and Stock Available'],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }

        Product::factory(12)->create(['category_id' => $furniture->id]);

        // Contacts (template-style)
        $contacts = [
            ['name' => 'Simon Ryles', 'position' => 'Full Stack Developer', 'email' => 'SimonRyles@minible.com', 'badge_class' => 'bg-success-subtle', 'badge_text_class' => 'text-success', 'projects' => 125, 'avatar' => 'assets/images/users/avatar-2.jpg'],
            ['name' => 'Marion Walker', 'position' => 'Frontend Developer', 'email' => 'MarionWalker@minible.com', 'badge_class' => 'bg-info-subtle', 'badge_text_class' => 'text-info', 'projects' => 132, 'avatar' => 'assets/images/users/avatar-3.jpg'],
            ['name' => 'Frederick White', 'position' => 'UI/UX Designer', 'email' => 'FrederickWhite@minible.com', 'badge_class' => 'bg-danger-subtle', 'badge_text_class' => 'text-danger', 'projects' => 112, 'avatar' => null],
            ['name' => 'Shanon Marvin', 'position' => 'Backend Developer', 'email' => 'ShanonMarvin@minible.com', 'badge_class' => 'bg-primary-subtle', 'badge_text_class' => 'text-primary', 'projects' => 121, 'avatar' => 'assets/images/users/avatar-4.jpg'],
            ['name' => 'Mark Jones', 'position' => 'Frontend Developer', 'email' => 'MarkJones@minible.com', 'badge_class' => 'bg-info-subtle', 'badge_text_class' => 'text-info', 'projects' => 145, 'avatar' => null],
            ['name' => 'Janice Morgan', 'position' => 'Backend Developer', 'email' => 'JaniceMorgan@minible.com', 'badge_class' => 'bg-primary-subtle', 'badge_text_class' => 'text-primary', 'projects' => 136, 'avatar' => 'assets/images/users/avatar-5.jpg'],
            ['name' => 'Patrick Petty', 'position' => 'UI/UX Designer', 'email' => 'PatrickPetty@minible.com', 'badge_class' => 'bg-danger-subtle', 'badge_text_class' => 'text-danger', 'projects' => 125, 'avatar' => 'assets/images/users/avatar-7.jpg'],
            ['name' => 'Marilyn Horton', 'position' => 'Backend Developer', 'email' => 'MarilynHorton@minible.com', 'badge_class' => 'bg-primary-subtle', 'badge_text_class' => 'text-primary', 'projects' => 154, 'avatar' => 'assets/images/users/avatar-8.jpg'],
            ['name' => 'Neal Womack', 'position' => 'Full Stack Developer', 'email' => 'NealWomack@minible.com', 'badge_class' => 'bg-success-subtle', 'badge_text_class' => 'text-success', 'projects' => 231, 'avatar' => 'assets/images/users/avatar-2.jpg'],
            ['name' => 'Phyllis Gatlin', 'position' => 'Full Stack Developer', 'email' => 'PhyllisGatlin@spy.com', 'phone' => '070 2860 5375', 'address' => '52 Ilchester MYBSTER 9WX', 'badge_class' => 'bg-success-subtle', 'badge_text_class' => 'text-success', 'projects' => 98, 'avatar' => 'assets/images/users/avatar-2.jpg', 'bio' => 'Curabitur non magna lobortis tempus gravida ornare libero sed diam sed fringilla est.'],
            ['name' => 'James Nix', 'position' => 'Full Stack Developer', 'email' => 'JamesNix@spy.com', 'phone' => '046 5685 6969', 'address' => '5 Boar Lane SELLING 2LG', 'badge_class' => 'bg-success-subtle', 'badge_text_class' => 'text-success', 'projects' => 87, 'avatar' => 'assets/images/users/avatar-1.jpg', 'bio' => 'Curabitur non magna lobortis tempus gravida ornare libero sed diam sed fringilla est.'],
            ['name' => 'Darlene Smith', 'position' => 'UI/UX Designer', 'email' => 'DarleneSmith@spy.com', 'phone' => '012 6587 1236', 'address' => '57 Guildry Street GAMRIE', 'badge_class' => 'bg-danger-subtle', 'badge_text_class' => 'text-danger', 'projects' => 76, 'avatar' => 'assets/images/users/avatar-3.jpg', 'bio' => 'Curabitur non magna lobortis tempus gravida ornare libero sed diam sed fringilla est.'],
            ['name' => 'William Swift', 'position' => 'Backend Developer', 'email' => 'WilliamSwift@spy.com', 'phone' => '012 6587 1236', 'address' => '80 South Street MONKW 7BR', 'badge_class' => 'bg-warning-subtle', 'badge_text_class' => 'text-warning', 'projects' => 141, 'avatar' => null, 'bio' => 'Curabitur non magna lobortis tempus gravida ornare libero sed diam sed fringilla est.'],
            ['name' => 'Kevin West', 'position' => 'Full Stack Developer', 'email' => 'KevinWest@spy.com', 'phone' => '052 6524 9896', 'address' => '88 Tadcaster PINCHBECK 6UB', 'badge_class' => 'bg-success-subtle', 'badge_text_class' => 'text-success', 'projects' => 165, 'avatar' => null, 'bio' => 'Curabitur non magna lobortis tempus gravida ornare libero sed diam sed fringilla est.'],
            ['name' => 'Tommy Hayes', 'position' => 'Backend Developer', 'email' => 'TommyHayes@spy.com', 'phone' => '065 2563 6587', 'address' => '5 Boar Lane SELLING 2LG', 'badge_class' => 'bg-warning-subtle', 'badge_text_class' => 'text-warning', 'projects' => 119, 'avatar' => 'assets/images/users/avatar-6.jpg', 'bio' => 'Curabitur non magna lobortis tempus gravida ornare libero sed diam sed fringilla est.'],
            ['name' => 'Diana Owens', 'position' => 'UI/UX Designer', 'email' => 'DianaOwens@spy.com', 'phone' => '087 6321 3235', 'address' => '52 Ilchester MYBSTER 9WX', 'badge_class' => 'bg-danger-subtle', 'badge_text_class' => 'text-danger', 'projects' => 102, 'avatar' => 'assets/images/users/avatar-8.jpg', 'bio' => 'Curabitur non magna lobortis tempus gravida ornare libero sed diam sed fringilla est.'],
            ['name' => 'Paul Sanchez', 'position' => 'Full Stack Developer', 'email' => 'PaulSanchez@spy.com', 'phone' => '021 0125 5689', 'address' => '80 South Street MONKW 7BR', 'badge_class' => 'bg-success-subtle', 'badge_text_class' => 'text-success', 'projects' => 154, 'avatar' => 'assets/images/users/avatar-4.jpg', 'bio' => 'Curabitur non magna lobortis tempus gravida ornare libero sed diam sed fringilla est.'],
            ['name' => 'Martin Gurley', 'position' => 'Full Stack Developer', 'email' => 'martingurley52@gmail.com', 'phone' => '+214 5632564', 'address' => 'New Your City, New Your, USA', 'badge_class' => 'bg-success-subtle', 'badge_text_class' => 'text-success', 'projects' => 210, 'avatar' => 'assets/images/users/avatar-3.jpg', 'bio' => 'Curabitur non magna lobortis tempus gravida ornare libero sed diam sed fringilla est.', 'zip' => '0005485', 'city' => 'New Your', 'country' => 'USA'],
        ];

        foreach ($contacts as $contact) {
            Contact::create($contact + [
                'bio' => $contact['bio'] ?? 'Curabitur non magna lobortis tempus gravida ornare libero sed diam sed fringilla est.',
                'phone' => $contact['phone'] ?? '0'.random_int(100000000, 999999999),
                'address' => $contact['address'] ?? '52 Ilchester MYBSTER 9WX',
                'city' => $contact['city'] ?? 'London',
                'country' => $contact['country'] ?? 'UK',
                'zip' => $contact['zip'] ?? '9WX',
            ]);
        }

        // Customers
        Customer::factory(12)->create();

        // Orders
        Order::factory(20)->create()->each(function (Order $order) {
            $product = Product::query()->inRandomOrder()->first();
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product?->id,
                'name' => $product?->name ?? 'Product',
                'quantity' => random_int(1, 5),
                'price' => $product?->price ?? 100,
            ]);
        });

        // Invoices
        Invoice::factory(15)->create()->each(function (Invoice $invoice) {
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'name' => 'Consulting Service',
                'description' => 'Monthly consulting retainer',
                'price' => $invoice->amount,
                'quantity' => 1,
            ]);
        });

        // Todos (template-style)
        $todos = [
            ['title' => 'Create a webadmin Dashboard', 'team' => 'Product Design', 'status' => 'Waiting', 'is_completed' => false, 'comments' => 7],
            ['title' => 'Create a New Landing', 'team' => 'Development', 'status' => 'Approved', 'is_completed' => true, 'comments' => 8],
            ['title' => 'Create a webadmin Logo', 'team' => 'Illustration', 'status' => 'Waiting', 'is_completed' => false, 'comments' => 5],
            ['title' => 'Redesign - Landing page', 'team' => 'Design', 'status' => 'Completed', 'is_completed' => false, 'comments' => 7],
            ['title' => 'Create a Blog Template', 'team' => 'Angular', 'status' => 'Waiting', 'is_completed' => false, 'comments' => 5],
            ['title' => 'Minimal Multipurpose Landing', 'team' => 'Backend', 'status' => 'Waiting', 'is_completed' => false, 'comments' => 2],
            ['title' => 'Fix Responsivenes for structure', 'team' => 'Development', 'status' => 'Completed', 'is_completed' => false, 'comments' => 5],
            ['title' => 'Create a webadmin Dashboard', 'team' => 'Illustration', 'status' => 'Approved', 'is_completed' => true, 'comments' => 9],
            ['title' => 'New Widgets For Dashboard', 'team' => 'Design', 'status' => 'Waiting', 'is_completed' => false, 'comments' => 10],
            ['title' => 'Old Landing Re-Design', 'team' => 'Angular', 'status' => 'Approved', 'is_completed' => true, 'comments' => 5],
            ['title' => 'Create a Landing Page', 'team' => 'Design', 'status' => 'Completed', 'is_completed' => false, 'comments' => 6],
        ];

        foreach ($todos as $todo) {
            $members = collect(range(1, random_int(1, 3)))->map(fn () => [
                'avatar' => random_int(0, 1) ? 'assets/images/users/avatar-'.random_int(1, 8).'.jpg' : null,
                'letter' => fake()->randomElement(['A', 'R', 'V', 'S']),
                'color' => fake()->randomElement(['bg-success', 'bg-warning', 'bg-primary', 'bg-danger', 'bg-info']),
            ])->all();

            Todo::create($todo + [
                'members' => $members,
                'due_date' => now()->addDays(random_int(1, 30)),
                'user_id' => User::query()->first()?->id,
            ]);
        }

        // Calendar events
        $events = [
            ['title' => 'All Day Event', 'start_date' => now()->startOfMonth(), 'class_name' => 'bg-primary', 'all_day' => true],
            ['title' => 'Long Event', 'start_date' => now()->subDays(5), 'end_date' => now()->subDays(2), 'class_name' => 'bg-warning', 'all_day' => true],
            ['title' => 'Repeating Event', 'start_date' => now()->subDays(3), 'class_name' => 'bg-info', 'all_day' => false],
            ['title' => 'Meeting', 'start_date' => now()->addDay(), 'class_name' => 'bg-success', 'all_day' => false],
            ['title' => 'Lunch', 'start_date' => now()->addDays(2), 'class_name' => 'bg-danger', 'all_day' => false],
            ['title' => 'Birthday Party', 'start_date' => now()->addDays(4), 'class_name' => 'bg-success', 'all_day' => true],
        ];

        foreach ($events as $event) {
            CalendarEvent::create($event + ['description' => 'Event details']);
        }

        // Chat
        $chatNames = [
            ['name' => 'Jennie Sherlock', 'avatar' => 'assets/images/users/avatar-6.jpg', 'status' => 'online', 'unread' => 1, 'last_message' => 'Hey, how are you?'],
            ['name' => 'Stacie Dube', 'avatar' => null, 'status' => 'online', 'unread' => 0, 'last_message' => 'Sounds good'],
            ['name' => 'Katie Olson', 'avatar' => 'assets/images/users/avatar-3.jpg', 'status' => 'away', 'unread' => 4, 'last_message' => 'See you tomorrow'],
            ['name' => 'Martin Gurley', 'avatar' => 'assets/images/users/avatar-3.jpg', 'status' => 'online', 'unread' => 0, 'last_message' => 'Meeting at 3pm'],
            ['name' => 'Scott Median', 'avatar' => 'assets/images/users/avatar-2.jpg', 'status' => 'online', 'unread' => 2, 'last_message' => 'Hello'],
            ['name' => 'Julian Rosa', 'avatar' => 'assets/images/users/avatar-3.jpg', 'status' => 'offline', 'unread' => 0, 'last_message' => 'What about our next..'],
            ['name' => 'David Medina', 'avatar' => 'assets/images/users/avatar-4.jpg', 'status' => 'online', 'unread' => 0, 'last_message' => 'Yeah everything is fine'],
            ['name' => 'Jay Baker', 'avatar' => 'assets/images/users/avatar-6.jpg', 'status' => 'away', 'unread' => 3, 'last_message' => 'Wow that\'s great'],
        ];

        foreach ($chatNames as $index => $chat) {
            $contact = ChatContact::create($chat);

            ChatMessage::create([
                'chat_contact_id' => $contact->id,
                'body' => 'Hey there! How is the project going?',
                'is_outgoing' => false,
                'day_label' => 'Today',
                'sent_at' => now()->subHours(3),
            ]);
            ChatMessage::create([
                'chat_contact_id' => $contact->id,
                'body' => 'Looking good. I will share the update shortly.',
                'is_outgoing' => true,
                'day_label' => 'Today',
                'sent_at' => now()->subHours(2),
            ]);
            ChatMessage::create([
                'chat_contact_id' => $contact->id,
                'body' => $chat['last_message'],
                'is_outgoing' => false,
                'day_label' => 'Today',
                'sent_at' => now()->subHour(),
            ]);
        }

        // Emails
        EmailMessage::factory(18)->create(['folder' => 'inbox']);
        EmailMessage::factory(6)->create(['folder' => 'sent']);

        // Files
        $files = [
            ['name' => 'Projects', 'type' => 'folder', 'size' => null, 'icon' => 'bx bx-folder', 'icon_class' => 'text-warning'],
            ['name' => 'Design', 'type' => 'folder', 'size' => null, 'icon' => 'bx bx-folder', 'icon_class' => 'text-warning'],
            ['name' => 'Images', 'type' => 'folder', 'size' => null, 'icon' => 'bx bx-folder', 'icon_class' => 'text-warning'],
            ['name' => 'webadmin-docs.pdf', 'type' => 'file', 'size' => '2.5 MB', 'icon' => 'bx bx-file', 'icon_class' => 'text-danger'],
            ['name' => 'dashboard-report.xlsx', 'type' => 'file', 'size' => '512 KB', 'icon' => 'bx bx-file', 'icon_class' => 'text-success'],
            ['name' => 'profile-photo.jpg', 'type' => 'file', 'size' => '1.2 MB', 'icon' => 'bx bx-image', 'icon_class' => 'text-primary'],
            ['name' => 'promo-video.mp4', 'type' => 'file', 'size' => '340 KB', 'icon' => 'bx bx-video', 'icon_class' => 'text-info'],
            ['name' => 'meeting-notes.docx', 'type' => 'file', 'size' => '89 KB', 'icon' => 'bx bx-file', 'icon_class' => 'text-primary'],
        ];

        foreach ($files as $file) {
            FileManagerItem::create($file + [
                'date_label' => now()->subDays(random_int(1, 90))->format('d M, Y'),
            ]);
        }

        // Notifications
        NotificationItem::factory(8)->create();
    }
}
