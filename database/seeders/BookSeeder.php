<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $books = $this->getBookData();

        foreach ($books as $bookData) {
            $categories = $bookData['categories'];
            unset($bookData['categories']);

            if (empty($bookData['slug'])) {
                $bookData['slug'] = Str::slug($bookData['title']);
            }

            $book = Book::firstOrCreate(
                ['book_code' => $bookData['book_code']],
                $bookData,
            );

            $categoryIds = Category::whereIn('name', $categories)->pluck('id');
            $book->categories()->syncWithoutDetaching($categoryIds);
        }
    }

    /**
     * Get all book seed data.
     *
     * @return array<int, array<string, mixed>>
     */
    private function getBookData(): array
    {
        return [
            // === Original 15 books from the old project ===
            [
                'book_code' => 'BK001',
                'title' => 'Another',
                'author' => 'Yukito Ayatsuji',
                'cover' => 'another.png',
                'published_year' => 2009,
                'synopsis' => 'In 1998, Koichi Sakakibara transfers to Yomiyama North Middle School. In class, he is drawn to a mysterious girl named Mei Misaki, who wears an eyepatch. As Koichi investigates, he discovers the dark history of Class 3-3 and a curse that has claimed the lives of students and their families for decades.',
                'categories' => ['Horror', 'Mystery', 'Supernatural', 'Gore', 'School'],
            ],
            [
                'book_code' => 'BK002',
                'title' => 'Dr. Stone',
                'author' => 'Riichiro Inagaki',
                'cover' => 'dr-stone.png',
                'published_year' => 2017,
                'synopsis' => 'After a mysterious flash of light turns all of humanity to stone, the extraordinarily intelligent Senku Ishigami awakens 3,700 years later to find the world has regressed to the Stone Age. With his scientific knowledge, he sets out to rebuild civilization from scratch.',
                'categories' => ['Adventure', 'Award Winning', 'Suspense', 'Survival'],
            ],
            [
                'book_code' => 'BK003',
                'title' => 'Grand Blue Dreaming',
                'author' => 'Kenji Inoue',
                'cover' => 'grand-blue-dreaming.png',
                'published_year' => 2014,
                'synopsis' => 'Iori Kitahara moves to a seaside town to attend college and live with his uncle\'s diving shop, Grand Blue. What he finds instead is a group of rowdy, hard-drinking upperclassmen who are part of the diving club. A hilarious college life comedy ensues.',
                'categories' => ['Comedy', 'Adult Cast', 'Gag Humor'],
            ],
            [
                'book_code' => 'BK004',
                'title' => 'Initial D',
                'author' => 'Shuichi Shigeno',
                'cover' => 'initial-d.png',
                'published_year' => 1995,
                'synopsis' => 'Takumi Fujiwara is an unassuming high school student who works at a gas station and delivers tofu for his father\'s shop every morning. Unbeknownst to most, his daily deliveries down the treacherous Mount Akina have made him an incredibly skilled downhill racer.',
                'categories' => ['Action', 'Drama', 'Racing'],
            ],
            [
                'book_code' => 'BK005',
                'title' => 'Kaguya-sama wa Kokurasetai',
                'author' => 'Aka Akasaka',
                'cover' => 'kaguya-sama.png',
                'published_year' => 2015,
                'synopsis' => 'Student council president Miyuki Shirogane and vice president Kaguya Shinomiya appear to be the perfect couple, but they both have too much pride to confess their feelings. The battle of wits and love begins as each tries to get the other to confess first.',
                'categories' => ['Award Winning', 'Comedy', 'Romance', 'School'],
            ],
            [
                'book_code' => 'BK006',
                'title' => 'Kimi no Koto ga Daidaidaidaidaisuki na 100-nin no Kanojo',
                'author' => 'Rikito Nakamura',
                'cover' => '100-kanojo.png',
                'published_year' => 2019,
                'synopsis' => 'Rentarou Aijou has been rejected 100 times in middle school. He visits a shrine and the God of Love tells him he has 100 destined soulmates. If any of them lose their connection, they will die—so Rentarou decides to date all 100 of them!',
                'categories' => ['Comedy', 'Romance', 'Harem', 'Parody', 'School'],
            ],
            [
                'book_code' => 'BK007',
                'title' => 'Komi-san wa, Comyushou desu',
                'author' => 'Tomohito Oda',
                'cover' => 'komi-san.png',
                'published_year' => 2016,
                'synopsis' => 'Shouko Komi is a beautiful and admired high school student, but she has a communication disorder that prevents her from speaking. When her classmate Hitohito Tadano discovers her secret, he vows to help her achieve her goal of making 100 friends.',
                'categories' => ['Award Winning', 'Comedy', 'School'],
            ],
            [
                'book_code' => 'BK008',
                'title' => 'Kono Subarashii Sekai ni Shukufuku wo!',
                'author' => 'Natsume Akatsuki',
                'cover' => 'konosuba.png',
                'published_year' => 2013,
                'synopsis' => 'After an embarrassing death, shut-in gamer Kazuma Satou is offered a choice by the goddess Aqua: go to heaven or be reborn in a fantasy world. He chooses the fantasy world and takes Aqua along as his companion, beginning a comedic adventure.',
                'categories' => ['Adventure', 'Comedy', 'Fantasy', 'Isekai', 'Parody'],
            ],
            [
                'book_code' => 'BK009',
                'title' => 'Kuzu no Honkai',
                'author' => 'Mengo Yokoyari',
                'cover' => 'kuzu-no-honkai.png',
                'published_year' => 2012,
                'synopsis' => 'High schoolers Hanabi Yasuraoka and Mugi Awaya appear to be the ideal couple, but both are in love with other people. They enter a relationship of convenience, using each other as substitutes for the ones they truly desire.',
                'categories' => ['Drama', 'Romance', 'School'],
            ],
            [
                'book_code' => 'BK010',
                'title' => 'MF Ghost',
                'author' => 'Shuichi Shigeno',
                'cover' => 'mf-ghost.png',
                'published_year' => 2017,
                'synopsis' => 'Set in a near-future Japan where self-driving cars dominate the roads, Kanata Livington, a graduate of a prestigious racing school, enters the MFG racing circuit with a Toyota 86 to search for his missing father and prove that human drivers still have a place.',
                'categories' => ['Racing'],
            ],
            [
                'book_code' => 'BK011',
                'title' => 'Nisekoi',
                'author' => 'Naoshi Komi',
                'cover' => 'nisekoi.png',
                'published_year' => 2011,
                'synopsis' => 'Raku Ichijou, the son of a yakuza boss, and Chitoge Kirisaki, the daughter of a rival gang leader, are forced to pretend to be lovers to keep peace between their families. Despite constantly bickering, they slowly develop real feelings.',
                'categories' => ['Comedy', 'Romance', 'Harem', 'School'],
            ],
            [
                'book_code' => 'BK012',
                'title' => 'Oshi no Ko',
                'author' => 'Aka Akasaka',
                'cover' => 'oshi-no-ko.webp',
                'published_year' => 2020,
                'synopsis' => 'A doctor who is a devoted fan of idol Ai Hoshino is murdered and reborn as her child. Now as Aquamarine Hoshino, he navigates the dark side of the entertainment industry while searching for the truth behind the events that shaped his and his mother\'s lives.',
                'categories' => ['Drama', 'Reincarnation', 'Showbiz'],
            ],
            [
                'book_code' => 'BK013',
                'title' => 'Tantei wa Mou Shindeiru',
                'author' => 'nigozyu',
                'cover' => 'tantei-wa-mou-shindeiru.png',
                'published_year' => 2019,
                'synopsis' => 'Kimihiko Kimizuka, a boy who constantly gets caught up in trouble, once served as the assistant to a legendary detective named Siesta. After Siesta\'s death, Kimihiko tries to live a normal life, but the legacy of the detective continues to pull him back.',
                'categories' => ['Comedy', 'Drama', 'Mystery', 'Romance'],
            ],
            [
                'book_code' => 'BK014',
                'title' => 'Tensei shitara Slime Datta Ken',
                'author' => 'Fuse',
                'cover' => 'tensei-slime.png',
                'published_year' => 2013,
                'synopsis' => 'A 37-year-old corporate worker is stabbed and reincarnated in a fantasy world as a slime—the weakest of monsters. However, he possesses unique abilities that allow him to absorb other creatures\' powers. He eventually builds a nation where humans and monsters coexist.',
                'categories' => ['Award Winning', 'Fantasy', 'Isekai', 'Reincarnation'],
            ],
            [
                'book_code' => 'BK015',
                'title' => 'Tokidoki Bosotto Russia-go de Dereru Tonari no Aalya-san',
                'author' => 'SunSunSun',
                'cover' => 'alya-san.png',
                'published_year' => 2021,
                'synopsis' => 'Alisa Mikhailovna Kujou, a half-Russian, half-Japanese beauty known as "Alya," sits next to the seemingly lazy Masachika Kuze. She often mutters sweet words in Russian, not knowing that Kuze actually understands Russian perfectly.',
                'categories' => ['Comedy', 'Romance', 'School'],
            ],

            // === 10 New Books ===
            [
                'book_code' => 'BK016',
                'title' => 'One Piece',
                'author' => 'Eiichiro Oda',
                'cover' => 'one-piece.jpg',
                'published_year' => 1997,
                'synopsis' => 'Monkey D. Luffy, a boy whose body gained rubber properties after eating a Devil Fruit, sets out on a grand adventure to find the legendary treasure "One Piece" and become the King of the Pirates. Along the way, he assembles a diverse crew of loyal nakama.',
                'categories' => ['Adventure', 'Comedy', 'Fantasy'],
            ],
            [
                'book_code' => 'BK017',
                'title' => 'Naruto',
                'author' => 'Masashi Kishimoto',
                'cover' => 'naruto.jpg',
                'published_year' => 1999,
                'synopsis' => 'Naruto Uzumaki, a young ninja shunned by his village for harboring a powerful fox demon, dreams of becoming Hokage—the strongest ninja and leader of his village. Through perseverance and bonds of friendship, he works to earn the respect of everyone around him.',
                'categories' => ['Action', 'Adventure', 'Fantasy'],
            ],
            [
                'book_code' => 'BK018',
                'title' => 'Attack on Titan',
                'author' => 'Hajime Isayama',
                'cover' => 'attack-on-titan.jpg',
                'published_year' => 2009,
                'synopsis' => 'Humanity lives within enormous walled cities to protect themselves from the Titans, gigantic humanoid creatures. When a colossal Titan breaches the outer wall, young Eren Yeager vows to exterminate every Titan and uncover the truth of their world.',
                'categories' => ['Action', 'Drama', 'Suspense', 'Gore'],
            ],
            [
                'book_code' => 'BK019',
                'title' => 'Death Note',
                'author' => 'Tsugumi Ohba',
                'cover' => 'death-note.jpg',
                'published_year' => 2003,
                'synopsis' => 'High school genius Light Yagami discovers a supernatural notebook that kills anyone whose name is written in it. He decides to use it to rid the world of criminals, but the genius detective L begins to close in on him in a deadly game of cat and mouse.',
                'categories' => ['Mystery', 'Suspense', 'Supernatural'],
            ],
            [
                'book_code' => 'BK020',
                'title' => 'Fullmetal Alchemist',
                'author' => 'Hiromu Arakawa',
                'cover' => 'fullmetal-alchemist.jpg',
                'published_year' => 2001,
                'synopsis' => 'Brothers Edward and Alphonse Elric attempt human transmutation to revive their mother, but the failed experiment costs Edward his arm and leg, and Alphonse his entire body. They embark on a journey to find the Philosopher\'s Stone and restore what they lost.',
                'categories' => ['Action', 'Adventure', 'Fantasy', 'Drama'],
            ],
            [
                'book_code' => 'BK021',
                'title' => 'Demon Slayer',
                'author' => 'Koyoharu Gotouge',
                'cover' => 'demon-slayer.jpg',
                'published_year' => 2016,
                'synopsis' => 'After his family is slaughtered by demons and his sister Nezuko is turned into one, gentle-hearted Tanjiro Kamado becomes a demon slayer to avenge his family and find a cure for his sister. His journey leads him through dangerous battles and unlikely alliances.',
                'categories' => ['Action', 'Supernatural', 'Fantasy'],
            ],
            [
                'book_code' => 'BK022',
                'title' => 'Spy × Family',
                'author' => 'Tatsuya Endo',
                'cover' => 'spy-family.jpg',
                'published_year' => 2019,
                'synopsis' => 'A spy known as "Twilight" must build a fake family for his next mission. He adopts a telepathic girl and marries an assassin—neither knowing the other\'s true identity. Together, this unusual family navigates espionage, school life, and genuine bonds.',
                'categories' => ['Action', 'Comedy'],
            ],
            [
                'book_code' => 'BK023',
                'title' => 'Chainsaw Man',
                'author' => 'Tatsuki Fujimoto',
                'cover' => 'chainsaw-man.jpg',
                'published_year' => 2018,
                'synopsis' => 'Denji, a destitute young man, merges with his pet devil dog Pochita to become Chainsaw Man—a human-devil hybrid with chainsaws protruding from his body. Recruited by a government agency, he hunts devils in exchange for a normal life.',
                'categories' => ['Action', 'Horror', 'Supernatural', 'Gore'],
            ],
            [
                'book_code' => 'BK024',
                'title' => 'Jujutsu Kaisen',
                'author' => 'Gege Akutami',
                'cover' => 'jujutsu-kaisen.jpg',
                'published_year' => 2018,
                'synopsis' => 'High schooler Yuji Itadori swallows a cursed finger belonging to the King of Curses, Ryomen Sukuna, and becomes his vessel. Enrolled in a school for jujutsu sorcerers, Yuji must consume all of Sukuna\'s fingers so they can be destroyed together.',
                'categories' => ['Action', 'Supernatural', 'Fantasy'],
            ],
            [
                'book_code' => 'BK025',
                'title' => 'Solo Leveling',
                'author' => 'Chugong',
                'cover' => 'solo-leveling.jpg',
                'published_year' => 2018,
                'synopsis' => 'In a world where hunters—humans with magical abilities—battle deadly monsters from portals called "gates," Sung Jinwoo is the weakest E-rank hunter. After a near-death experience in a dungeon, he gains a unique ability to level up without limit.',
                'categories' => ['Action', 'Fantasy', 'Adventure'],
            ],
        ];
    }
}
