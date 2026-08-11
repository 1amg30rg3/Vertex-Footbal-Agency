<?php

namespace Database\Seeders;

use App\Models\News;
use App\Models\NewsCategory;
use App\Models\Player;
use App\Models\TeamMember;
use App\Models\Trainer;
use App\Models\User;
use App\Support\Locales;
use Illuminate\Database\Seeder;

class DemoContentSeeder extends Seeder
{
    public function run(): void
    {
        $author = User::where('role', 'admin')->first();

        $this->categories();
        $this->players();
        $this->trainers();
        $this->team();
        $this->news($author);
    }

    protected function categories(): void
    {
        $categories = [
            ['slug' => 'transfers', 'color' => '#3D8AD4', 'ka' => 'ტრანსფერები', 'en' => 'Transfers'],
            ['slug' => 'match-reports', 'color' => '#AC7D03', 'ka' => 'მატჩის ანგარიში', 'en' => 'Match reports'],
            ['slug' => 'agency', 'color' => '#C3608A', 'ka' => 'სააგენტო', 'en' => 'Agency'],
        ];

        foreach ($categories as $index => $category) {
            NewsCategory::updateOrCreate(
                ['slug' => $category['slug']],
                [
                    'name' => $this->map(['ka' => $category['ka'], 'en' => $category['en']]),
                    'color' => $category['color'],
                    'sort_order' => $index,
                ],
            );
        }
    }

    protected function players(): void
    {
        $becker = Player::updateOrCreate(
            ['slug' => 'maximilian-becker'],
            [
                'first_name' => $this->map(['ka' => 'მაქსიმილიან', 'en' => 'Maximilian', 'de' => 'Maximilian']),
                'last_name' => $this->map(['ka' => 'ბეკერი', 'en' => 'Becker', 'de' => 'Becker']),
                'date_of_birth' => '2004-03-18',
                'nationality' => 'Germany',
                'height_cm' => 183,
                'weight_kg' => 76,
                'position' => 'midfielder',
                'specific_position' => $this->map([
                    'ka' => 'ცენტრალური ნახევარმცველი',
                    'en' => 'Central Midfielder',
                    'de' => 'Zentraler Mittelfeldspieler',
                ]),
                'preferred_foot' => 'right',
                'current_club' => 'SV Waldhof Mannheim',
                'contract_until' => '2027-06-30',
                'phone' => '+49 151 000 00 00',
                'email' => 'max.becker@example.com',
                'instagram' => '@max.becker',
                'city' => 'Mannheim',
                'country' => 'Germany',
                'playing_style' => $this->map([
                    'ka' => '<p>მაქსიმილიანი არის ინტელექტუალური ნახევარმცველი, რომელიც თამაშის ტემპს კარგად კითხულობს. მისი ძლიერი მხარეებია ზუსტი გრძელი გადაცემები, პრესინგის დროს სწორი პოზიციის შერჩევა და მუდმივი მუშაობა მოედანზე.</p>',
                    'en' => '<p>Maximilian is an intelligent central midfielder who reads the tempo of a game well. His strengths are accurate long passing, positional discipline under pressure and a relentless work rate across the full ninety minutes.</p>',
                    'de' => '<p>Maximilian ist ein intelligenter zentraler Mittelfeldspieler, der das Spieltempo gut liest. Seine Stärken sind präzise lange Bälle, taktische Disziplin unter Druck und eine hohe Laufbereitschaft.</p>',
                ]),
                'pitch_x' => 50.0,
                'pitch_y' => 58.0,
                'goals_short_term' => $this->map([
                    'ka' => '<ul><li>ძირითად შემადგენლობაში დამკვიდრება</li><li>სეზონში 30+ მატჩის ჩატარება</li></ul>',
                    'en' => '<ul><li>Establish himself in the starting eleven</li><li>Play 30+ competitive matches this season</li></ul>',
                ]),
                'goals_mid_term' => $this->map([
                    'ka' => '<ul><li>მე-2 ბუნდესლიგაში გადასვლა</li><li>ეროვნულ ახალგაზრდულ ნაკრებში გამოძახება</li></ul>',
                    'en' => '<ul><li>Move up to the 2. Bundesliga</li><li>Earn a call-up to a national youth squad</li></ul>',
                ]),
                'goals_long_term' => $this->map([
                    'ka' => '<ul><li>უმაღლეს დივიზიონში თამაში</li><li>გუნდის კაპიტნობა</li></ul>',
                    'en' => '<ul><li>Compete in a top-flight league</li><li>Captain a professional side</li></ul>',
                ]),
                'quote' => $this->map([
                    'ka' => 'დისციპლინა ის არის, რაც ნიჭს შედეგად აქცევს.',
                    'en' => 'Discipline is what turns talent into results.',
                    'de' => 'Disziplin macht aus Talent Ergebnisse.',
                ]),
                'status' => 'published',
                'sort_order' => 0,
                'is_featured' => true,
                'seo_description' => $this->map([
                    'en' => 'Maximilian Becker — central midfielder represented by VERTEX Football Agency.',
                ]),
            ],
        );

        $this->beckerDetails($becker);

        $kartveli = Player::updateOrCreate(
            ['slug' => 'giorgi-kartveli'],
            [
                'first_name' => $this->map(['ka' => 'გიორგი', 'en' => 'Giorgi']),
                'last_name' => $this->map(['ka' => 'ქართველი', 'en' => 'Kartveli']),
                'date_of_birth' => '2002-09-04',
                'nationality' => 'Georgia',
                'height_cm' => 189,
                'weight_kg' => 84,
                'position' => 'defender',
                'specific_position' => $this->map(['ka' => 'ცენტრალური მცველი', 'en' => 'Centre Back']),
                'preferred_foot' => 'left',
                'current_club' => 'FC Dinamo Tbilisi',
                'contract_until' => '2026-12-31',
                'city' => 'Tbilisi',
                'country' => 'Georgia',
                'playing_style' => $this->map([
                    'ka' => '<p>ძლიერი საჰაერო დუელებში, თავდაჯერებული ბურთის გატანისას და ლიდერი დაცვის ხაზში.</p>',
                    'en' => '<p>Dominant in aerial duels, comfortable carrying the ball out of defence and a natural organiser of the back line.</p>',
                ]),
                'pitch_x' => 50.0,
                'pitch_y' => 82.0,
                'status' => 'published',
                'sort_order' => 1,
                'is_featured' => true,
            ],
        );

        $this->basicSkills($kartveli);

        if (Player::count() < 8) {
            Player::factory()
                ->count(8 - Player::count())
                ->create()
                ->each(fn (Player $player, int $index) => $player->update(['sort_order' => $index + 2]));
        }
    }

    protected function beckerDetails(Player $player): void
    {
        $player->skills()->delete();

        $skills = [
            ['ka' => 'თამაშის ინტელექტი', 'en' => 'Game Intelligence', 'de' => 'Spielintelligenz', 'value' => 88],
            ['ka' => 'პასის სიზუსტე', 'en' => 'Passing Accuracy', 'de' => 'Passgenauigkeit', 'value' => 85],
            ['ka' => 'დუელები / შერჩევა', 'en' => 'Duel / Tackling', 'de' => 'Zweikampf', 'value' => 78],
            ['ka' => 'გამძლეობა', 'en' => 'Work Rate / Stamina', 'de' => 'Ausdauer', 'value' => 91],
            ['ka' => 'ტაქტიკური გაგება', 'en' => 'Tactical Understanding', 'de' => 'Taktisches Verständnis', 'value' => 84],
            ['ka' => 'დარტყმის ძალა', 'en' => 'Shot Power', 'de' => 'Schusskraft', 'value' => 72],
        ];

        foreach ($skills as $index => $skill) {
            $player->skills()->create([
                'label' => $this->map(['ka' => $skill['ka'], 'en' => $skill['en'], 'de' => $skill['de']]),
                'value' => $skill['value'],
                'sort_order' => $index,
            ]);
        }

        $player->careerEntries()->delete();

        $career = [
            [
                'club' => 'SV Waldhof Mannheim',
                'from' => '2023-07-01',
                'to' => null,
                'category' => 'Senior',
                'league' => ['ka' => '3. ლიგა', 'en' => '3. Liga', 'de' => '3. Liga'],
            ],
            [
                'club' => 'TSG Hoffenheim U19',
                'from' => '2021-07-01',
                'to' => '2023-06-30',
                'category' => 'U19',
                'league' => ['ka' => 'ბუნდესლიგა U19', 'en' => 'U19 Bundesliga', 'de' => 'A-Junioren-Bundesliga'],
            ],
            [
                'club' => 'TSG Hoffenheim U17',
                'from' => '2019-07-01',
                'to' => '2021-06-30',
                'category' => 'U17',
                'league' => ['ka' => 'ბუნდესლიგა U17', 'en' => 'U17 Bundesliga', 'de' => 'B-Junioren-Bundesliga'],
            ],
        ];

        foreach ($career as $index => $entry) {
            $player->careerEntries()->create([
                'club_name' => $entry['club'],
                'started_on' => $entry['from'],
                'ended_on' => $entry['to'],
                'category' => $entry['category'],
                'league' => $this->map($entry['league']),
                'sort_order' => $index,
            ]);
        }

        $player->achievements()->delete();

        $achievements = [
            ['ka' => 'გუნდის ასვლა მე-3 ლიგაში', 'en' => 'Promotion to the 3. Liga', 'year' => '2024'],
            ['ka' => 'U19 გუნდის კაპიტანი', 'en' => 'Captain of the U19 squad', 'year' => '2023'],
            ['ka' => 'სეზონის საუკეთესო ახალგაზრდა მოთამაშე', 'en' => 'Young Player of the Season', 'year' => '2022'],
        ];

        foreach ($achievements as $index => $achievement) {
            $player->achievements()->create([
                'text' => $this->map(['ka' => $achievement['ka'], 'en' => $achievement['en']]),
                'year' => $achievement['year'],
                'sort_order' => $index,
            ]);
        }

        $player->seasons()->delete();

        $seasons = [
            [
                'label' => '2024/2025',
                'club' => 'SV Waldhof Mannheim',
                'matches' => 31, 'goals' => 6, 'assists' => 9, 'minutes' => 2564,
                'start' => 74, 'sub' => 18, 'out' => 8,
                'current' => true,
                'months' => [
                    [8, 1, 1], [9, 0, 2], [10, 1, 0], [11, 2, 1], [12, 0, 1],
                    [1, 0, 0], [2, 1, 2], [3, 1, 1], [4, 0, 1], [5, 0, 0],
                ],
            ],
            [
                'label' => '2023/2024',
                'club' => 'SV Waldhof Mannheim',
                'matches' => 24, 'goals' => 3, 'assists' => 5, 'minutes' => 1622,
                'start' => 48, 'sub' => 34, 'out' => 18,
                'current' => false,
                'months' => [
                    [8, 0, 0], [9, 1, 1], [10, 0, 1], [11, 0, 0], [12, 1, 0],
                    [1, 0, 1], [2, 0, 0], [3, 1, 1], [4, 0, 1], [5, 0, 0],
                ],
            ],
        ];

        foreach ($seasons as $index => $data) {
            $season = $player->seasons()->create([
                'label' => $data['label'],
                'club_name' => $data['club'],
                'matches_played' => $data['matches'],
                'goals' => $data['goals'],
                'assists' => $data['assists'],
                'minutes_played' => $data['minutes'],
                'starting_pct' => $data['start'],
                'substitute_pct' => $data['sub'],
                'not_in_squad_pct' => $data['out'],
                'is_current' => $data['current'],
                'sort_order' => $index,
            ]);

            foreach ($data['months'] as $order => [$month, $goals, $assists]) {
                $season->months()->create([
                    'month' => $month,
                    'goals' => $goals,
                    'assists' => $assists,
                    'sort_order' => $order,
                ]);
            }
        }
    }

    protected function basicSkills(Player $player): void
    {
        if ($player->skills()->exists()) {
            return;
        }

        $skills = [
            ['ka' => 'საჰაერო დუელები', 'en' => 'Aerial Duels', 'value' => 90],
            ['ka' => 'პოზიციონირება', 'en' => 'Positioning', 'value' => 83],
            ['ka' => 'პასის სიზუსტე', 'en' => 'Passing Accuracy', 'value' => 76],
            ['ka' => 'ლიდერობა', 'en' => 'Leadership', 'value' => 87],
        ];

        foreach ($skills as $index => $skill) {
            $player->skills()->create([
                'label' => $this->map(['ka' => $skill['ka'], 'en' => $skill['en']]),
                'value' => $skill['value'],
                'sort_order' => $index,
            ]);
        }
    }

    protected function trainers(): void
    {
        $trainer = Trainer::updateOrCreate(
            ['slug' => 'lasha-beridze'],
            [
                'first_name' => $this->map(['ka' => 'ლაშა', 'en' => 'Lasha']),
                'last_name' => $this->map(['ka' => 'ბერიძე', 'en' => 'Beridze']),
                'role' => $this->map(['ka' => 'განვითარების ხელმძღვანელი', 'en' => 'Head of Development']),
                'bio' => $this->map([
                    'ka' => '<p>ლაშას აქვს თხუთმეტწლიანი გამოცდილება ახალგაზრდული ფეხბურთის განვითარებაში. მუშაობდა როგორც აკადემიებში, ისე პროფესიულ გუნდებში.</p>',
                    'en' => '<p>Lasha brings fifteen years of youth-development experience, split between academy work and first-team coaching.</p>',
                ]),
                'nationality' => 'Georgia',
                'date_of_birth' => '1982-05-11',
                'email' => 'lasha@example.com',
                'status' => 'published',
                'sort_order' => 0,
            ],
        );

        if (! $trainer->workEntries()->exists()) {
            $history = [
                ['org' => 'VERTEX Football Agency', 'from' => '2021-01-01', 'to' => null, 'ka' => 'განვითარების ხელმძღვანელი', 'en' => 'Head of Development'],
                ['org' => 'FC Dinamo Tbilisi Academy', 'from' => '2015-07-01', 'to' => '2020-12-31', 'ka' => 'U19 მთავარი მწვრთნელი', 'en' => 'U19 Head Coach'],
                ['org' => 'FC Locomotive Tbilisi', 'from' => '2010-01-01', 'to' => '2015-06-30', 'ka' => 'მწვრთნელის ასისტენტი', 'en' => 'Assistant Coach'],
            ];

            foreach ($history as $index => $entry) {
                $trainer->workEntries()->create([
                    'organization' => $entry['org'],
                    'title' => $this->map(['ka' => $entry['ka'], 'en' => $entry['en']]),
                    'started_on' => $entry['from'],
                    'ended_on' => $entry['to'],
                    'sort_order' => $index,
                ]);
            }
        }

        if (Trainer::count() < 4) {
            Trainer::factory()->count(4 - Trainer::count())->create();
        }
    }

    protected function team(): void
    {
        $members = [
            ['slug' => 'ana-kvaratskhelia', 'ka' => 'ანა კვარაცხელია', 'en' => 'Ana Kvaratskhelia', 'role_ka' => 'დამფუძნებელი', 'role_en' => 'Founder'],
            ['slug' => 'nino-tsiklauri', 'ka' => 'ნინო წიკლაური', 'en' => 'Nino Tsiklauri', 'role_ka' => 'იურისტი', 'role_en' => 'Legal Counsel'],
            ['slug' => 'david-meier', 'ka' => 'დავით მაიერი', 'en' => 'David Meier', 'role_ka' => 'სკაუტინგის ხელმძღვანელი', 'role_en' => 'Head of Scouting'],
        ];

        foreach ($members as $index => $member) {
            TeamMember::updateOrCreate(
                ['slug' => $member['slug']],
                [
                    'name' => $this->map(['ka' => $member['ka'], 'en' => $member['en']]),
                    'role' => $this->map(['ka' => $member['role_ka'], 'en' => $member['role_en']]),
                    'bio' => $this->map([
                        'ka' => '<p>სააგენტოს გუნდის წევრი, რომელიც პასუხისმგებელია მოთამაშეების ყოველდღიურ მხარდაჭერაზე.</p>',
                        'en' => '<p>Part of the agency team responsible for the day-to-day support of our athletes.</p>',
                    ]),
                    'email' => str($member['en'])->lower()->replace(' ', '.')->append('@example.com')->toString(),
                    'social_links' => [['platform' => 'linkedin', 'url' => 'https://linkedin.com/']],
                    'status' => 'published',
                    'sort_order' => $index,
                ],
            );
        }
    }

    protected function news(?User $author): void
    {
        $categories = NewsCategory::pluck('id', 'slug');

        $articles = [
            [
                'slug' => 'maximilian-becker-extends-contract',
                'category' => 'transfers',
                'featured' => 0,
                'ka_title' => 'მაქსიმილიან ბეკერმა კონტრაქტი 2027 წლამდე გაახანგრძლივა',
                'en_title' => 'Maximilian Becker extends his contract until 2027',
                'ka_excerpt' => 'ცენტრალურმა ნახევარმცველმა კლუბთან ახალი სამწლიანი შეთანხმება გააფორმა.',
                'en_excerpt' => 'The central midfielder has signed a new three-year deal with his club.',
            ],
            [
                'slug' => 'agency-opens-scouting-office',
                'category' => 'agency',
                'featured' => 1,
                'ka_title' => 'სააგენტომ ახალი სკაუტინგის ოფისი გახსნა',
                'en_title' => 'The agency opens a new scouting office',
                'ka_excerpt' => 'ახალი ოფისი გააძლიერებს ევროპულ ბაზარზე ჩვენს ყოფნას.',
                'en_excerpt' => 'The new office strengthens our presence across the European market.',
            ],
            [
                'slug' => 'season-review-2024-2025',
                'category' => 'match-reports',
                'featured' => 2,
                'ka_title' => 'სეზონის მიმოხილვა 2024/2025',
                'en_title' => 'Season review 2024/2025',
                'ka_excerpt' => 'ჩვენი მოთამაშეების საუკეთესო მომენტები გასული სეზონიდან.',
                'en_excerpt' => 'The standout moments from our players over the past season.',
            ],
        ];

        foreach ($articles as $index => $article) {
            News::updateOrCreate(
                ['slug' => $article['slug']],
                [
                    'news_category_id' => $categories[$article['category']] ?? null,
                    'user_id' => $author?->id,
                    'title' => $this->map(['ka' => $article['ka_title'], 'en' => $article['en_title']]),
                    'excerpt' => $this->map(['ka' => $article['ka_excerpt'], 'en' => $article['en_excerpt']]),
                    'body' => $this->map([
                        'ka' => '<p>'.$article['ka_excerpt'].'</p><p>ვრცელი ტექსტი ხელმისაწვდომია ადმინ პანელიდან რედაქტირებისთვის — თითოეული ენისთვის ცალკე.</p>',
                        'en' => '<p>'.$article['en_excerpt'].'</p><p>The full body copy is editable from the admin panel, independently for each of the seven languages.</p>',
                    ]),
                    'status' => 'published',
                    'published_at' => now()->subDays(($index + 1) * 6),
                    'is_featured' => true,
                    'featured_order' => $article['featured'],
                ],
            );
        }

        if (News::count() < 12) {
            News::factory()
                ->count(12 - News::count())
                ->create([
                    'user_id' => $author?->id,
                    'news_category_id' => $categories->values()->random(),
                ]);
        }

        News::factory()->draft()->create(['user_id' => $author?->id]);
        News::factory()->scheduled()->create(['user_id' => $author?->id]);
    }

    protected function map(array $values): array
    {
        return array_merge(Locales::blankMap(), array_intersect_key($values, array_flip(Locales::codes())));
    }
}
