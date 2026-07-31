<?php

namespace Database\Seeders;

use App\Models\Badge;
use App\Models\Election;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Exam;
use App\Models\Meeting;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class ProductionSeeder extends Seeder
{
    private static ?string $password = null;

    private array $userIds = [];

    private array $activeUserIds = [];

    private array $eventIds = [];

    private array $meetingIds = [];

    private int $adminId;

    private Carbon $now;

    public function run(): void
    {
        $this->now = Carbon::parse('2026-07-28 12:00:00');

        $this->command?->info('Seeding 5,000 production members...');

        $this->seedUsers();
        $this->seedMemberships();
        $this->seedMemberProfiles();
        $this->seedSocialLinks();
        $this->seedUserPrivacies();
        $this->seedNotificationPreferences();
        $this->seedGamificationStats();
        $this->seedEvents();
        $this->seedEventRegistrations();
        $this->seedEventAttendance();
        $this->seedEventFeedback();
        $this->seedMeetings();
        $this->seedCtfCompetitions();
        $this->seedPolls();
        $this->seedElections();
        $this->seedExams();
        $this->seedFines();
        $this->seedAnnouncements();
        $this->seedClubResourceProgress();
        $this->seedBadgeAwards();
        $this->seedPointTransactions();

        $this->command?->info('Production seeding complete!');
    }

    private function password(): string
    {
        return self::$password ??= Hash::make('password');
    }

    private function randomElement(array $array): mixed
    {
        return $array[array_rand($array)];
    }

    private function randomElements(array $array, int $count): array
    {
        $count = min($count, count($array));
        $keys = (array) array_rand($array, $count);

        return array_map(fn ($k) => $array[$k], $keys);
    }

    private function randomFloat(float $min, float $max, int $decimals = 2): float
    {
        return round($min + mt_rand() / mt_getrandmax() * ($max - $min), $decimals);
    }

    private function randomBool(int $truePercentage = 50): bool
    {
        return mt_rand(1, 100) <= $truePercentage;
    }

    private function randomDate(Carbon $start, Carbon $end): Carbon
    {
        $diff = $start->diffInSeconds($end);

        return (clone $start)->addSeconds(mt_rand(0, max(0, $diff)));
    }

    private function emailFromName(string $name, int $suffix = 0): string
    {
        $local = preg_replace('/[^a-z0-9.]/i', '.', str_replace(' ', '.', $name));
        $local = strtolower(trim($local, '.'));

        return $suffix > 0 ? "{$local}.{$suffix}@students.slau-csic.org" : "{$local}@students.slau-csic.org";
    }

    private function nameData(): array
    {
        return [
            'ugandan' => [
                'male_first' => [
                    'Kato', 'Wasswa', 'Mukasa', 'Lwanga', 'Kiwanuka', 'Kizza', 'Muwonge',
                    'Ssali', 'Bbosa', 'Kayondo', 'Mbabazi', 'Byaruhanga', 'Mugisha',
                    'Karuhanga', 'Baluku', 'Opio', 'Odong', 'Okello', 'Ochen', 'Ojok',
                    'Otim', 'Arinaitwe', 'Niwagaba', 'Twesige', 'Mwebesa', 'Muhangi',
                    'Tumwesigye', 'Lule', 'Nsubuga', 'Sserwadda', 'Mutyaba', 'Nkalubo',
                    'Ssempijja', 'Turyahikayo', 'Muzaale', 'Nkonyi', 'Kuteesa', 'Biraro',
                    'Emuron', 'Opedun', 'Ocen', 'Obbo', 'Olela', 'Omoding', 'Okurut',
                ],
                'female_first' => [
                    'Nambi', 'Nakato', 'Babirye', 'Nalwanga', 'Namazzi', 'Nandawula',
                    'Nakamya', 'Nanteza', 'Kemigisha', 'Natukunda', 'Ndyanabo', 'Nyangoma',
                    'Atukunda', 'Aber', 'Acen', 'Adong', 'Akello', 'Amulen', 'Apio',
                    'Auma', 'Lamunu', 'Nabatanzi', 'Nabwami', 'Nakabugo', 'Nakanwagi',
                    'Nakazzi', 'Nalule', 'Namakula', 'Namiiro', 'Namuddu', 'Namugenyi',
                    'Nandutu', 'Nanfuka', 'Nankwanga', 'Nassali', 'Tibesigwa', 'Nabunya',
                    'Nagawa', 'Nagudi', 'Nakibuuka', 'Nakimuli', 'Nalubega', 'Nalwoga',
                ],
                'last' => [
                    'Kato', 'Wasswa', 'Mukasa', 'Lwanga', 'Ssali', 'Kizza', 'Muwonge',
                    'Kiwanuka', 'Nsubuga', 'Mbabazi', 'Byaruhanga', 'Mugisha',
                    'Turyahikayo', 'Otim', 'Okello', 'Odong', 'Ochen', 'Opio', 'Ojok',
                    'Niwagaba', 'Twesige', 'Muhangi', 'Baluku', 'Muzaale', 'Nkonyi',
                    'Emuron', 'Opedun', 'Ocen', 'Semwanga', 'Sserwadda', 'Mutebi',
                    'Kintu', 'Walakira', 'Sekandi', 'Buwembo', 'Kiggundu', 'Lubega',
                ],
            ],
            'south_sudanese' => [
                'male_first' => [
                    'Akol', 'Ater', 'Bol', 'Chol', 'Deng', 'Duop', 'Garang', 'Gok',
                    'Jal', 'Jok', 'Kuol', 'Lual', 'Mach', 'Malek', 'Malith', 'Manyang',
                    'Mabor', 'Madut', 'Majok', 'Makur', 'Mijak', 'Monyluak', 'Mou',
                    'Mut', 'Ngong', 'Ngundeng', 'Nyang', 'Riak', 'Ring', 'Riek',
                    'Ruot', 'Thon', 'Tot', 'Tut', 'Wal', 'Wol', 'Yel', 'Yien', 'Yom',
                ],
                'female_first' => [
                    'Abuk', 'Achol', 'Adau', 'Adhel', 'Ajok', 'Akon', 'Akur', 'Aluel',
                    'Amel', 'Amuor', 'Anyieth', 'Apath', 'Arual', 'Atak', 'Ateny',
                    'Athiei', 'Atong', 'Awak', 'Awel', 'Ayai', 'Ayen', 'Ayer',
                    'Ayom', 'Ayon', 'Bakhita', 'Duol', 'Makuac', 'Manut', 'Naker',
                    'Nana', 'Nyanar', 'Nyang', 'Nyanluak', 'Nyibol', 'Nynut', 'Piol', 'Yar',
                ],
                'last' => [
                    'Akol', 'Ater', 'Bol', 'Chol', 'Deng', 'Duop', 'Garang', 'Jal',
                    'Jok', 'Kuol', 'Lual', 'Mach', 'Malek', 'Manyang', 'Majok',
                    'Makur', 'Nyang', 'Riak', 'Ring', 'Riek', 'Ruot', 'Tut', 'Wol', 'Yor',
                    'Malith', 'Mabor', 'Madut', 'Monyluak', 'Ngundeng', 'Thon',
                ],
            ],
            'ethiopian' => [
                'male_first' => [
                    'Abay', 'Abebe', 'Abera', 'Abiy', 'Admasu', 'Alem', 'Alemu',
                    'Amanuel', 'Assefa', 'Ayele', 'Bekele', 'Berhanu', 'Dagne', 'Dawit',
                    'Dejene', 'Elias', 'Endale', 'Ephrem', 'Eshetu', 'Eyasu', 'Fikru',
                    'Fisseha', 'Gebre', 'Habtamu', 'Hailu', 'Henok', 'Kaleb', 'Kebede',
                    'Kidane', 'Lemma', 'Mamo', 'Melaku', 'Meles', 'Meron', 'Mesfin',
                    'Mulugeta', 'Negash', 'Negussie', 'Nega', 'Samson', 'Solomon',
                    'Tadesse', 'Taye', 'Tekle', 'Tesfaye', 'Tilahun', 'Worku',
                    'Yared', 'Yohannes', 'Yonas', 'Yoseph', 'Zemedkun',
                ],
                'female_first' => [
                    'Aida', 'Alemitu', 'Almaz', 'Aregash', 'Askale', 'Aynalem', 'Azeb',
                    'Belaynesh', 'Bezawit', 'Birhan', 'Bizunesh', 'Desta', 'Eden',
                    'Edna', 'Eleni', 'Emebet', 'Eyerusalem', 'Fikirte', 'Firehiwot',
                    'Genet', 'Gennet', 'Hadas', 'Hanna', 'Haregewoin', 'Hiwot',
                    'Konjit', 'Lemlem', 'Lidya', 'Loza', 'Makeda', 'Mekdes',
                    'Merhawit', 'Meseret', 'Mulu', 'Rahel', 'Roman', 'Sara', 'Saron',
                    'Seble', 'Selam', 'Senait', 'Tirhas', 'Tsedale', 'Tsion',
                    'Weini', 'Winta', 'Worknesh', 'Yalem', 'Yordanos', 'Zebiba',
                ],
                'last' => [
                    'Abate', 'Abay', 'Abera', 'Alemu', 'Amanuel', 'Asfaw', 'Assefa',
                    'Ayele', 'Bekele', 'Belay', 'Berhanu', 'Desta', 'Fikru', 'Gebre',
                    'Gebremichael', 'Gizaw', 'Hailu', 'Kebede', 'Kidane', 'Lemma',
                    'Mamo', 'Mekonnen', 'Meles', 'Melaku', 'Mengistu', 'Mesfin',
                    'Negash', 'Negussie', 'Tadesse', 'Tekle', 'Tesfaye', 'Tilahun',
                    'Worku', 'Yimer', 'Yohannes', 'Zewde', 'Wondimu', 'Wubneh',
                ],
            ],
            'congolese' => [
                'male_first' => [
                    'Amani', 'Bahati', 'Baraka', 'Bosco', 'Bwanga', 'Byamungu',
                    'Chibalonza', 'Deo', 'Didier', 'Dunia', 'Esala', 'Fataki',
                    'Gedeon', 'Habimana', 'Ilunga', 'Kabamba', 'Kabeya', 'Kabongo',
                    'Kalala', 'Kambale', 'Kamwanya', 'Kanyinda', 'Kasereka', 'Kasongo',
                    'Katembo', 'Kavira', 'Kazadi', 'Kitenge', 'Kiyombo', 'Kwete',
                    'Lemba', 'Likasi', 'Lokwa', 'Londa', 'Lubaki', 'Lukusa',
                    'Lumbala', 'Lunda', 'Lusamba', 'Lutumba', 'Mabiala', 'Mabula',
                    'Mafuta', 'Maisha', 'Majani', 'Makasi', 'Makela', 'Makiese',
                    'Malembe', 'Maloba', 'Malonda', 'Manda', 'Mangala', 'Matondo',
                    'Mavungu', 'Mawazo', 'Mayele', 'Mayimona',
                ],
                'female_first' => [
                    'Aisha', 'Akili', 'Amisa', 'Asha', 'Bahati', 'Baraka', 'Bintu',
                    'Bora', 'Debora', 'Dorcas', 'Esperance', 'Faida', 'Fatuma',
                    'Furaha', 'Grace', 'Hawa', 'Hidaya', 'Irene', 'Jambo', 'Jamila',
                    'Joli', 'Joyeuse', 'Kaori', 'Kashi', 'Kavira', 'Kazima',
                    'Kumwimba', 'Likasi', 'Loleka', 'Londa', 'Lubunga', 'Lumueno',
                    'Lusamba', 'Maboko', 'Maisha', 'Makasi', 'Maloba', 'Mambweni',
                    'Mandefu', 'Mangala', 'Mapenzi', 'Mapinda', 'Masika', 'Matondo',
                    'Mawazo', 'Mayele', 'Mukambilwa', 'Muliwavyo', 'Mwadi', 'Mwajuma',
                    'Mwami', 'Mwanahawa', 'Mwangaza', 'Nathalie', 'Neema', 'Nefertiti',
                    'Ngoy', 'Nsimire', 'Nyota', 'Pamela', 'Pendo', 'Pili', 'Pitanga',
                    'Raissa', 'Rehema', 'Safiya', 'Salamu', 'Salima', 'Samba',
                    'Sefu', 'Shabani', 'Shamim', 'Shindano', 'Sifa', 'Subira',
                    'Tabu', 'Tatu', 'Therese', 'Tshiabu', 'Tshilomba', 'Tshimanga',
                    'Tshomba', 'Tshoto', 'Ubah', 'Upendo', 'Ushindi', 'Veronique',
                    'Yvonne', 'Zawadi', 'Zena', 'Zuri',
                ],
                'last' => [
                    'Amani', 'Bahati', 'Baraka', 'Bosco', 'Byamungu', 'Dunia',
                    'Ilunga', 'Kabamba', 'Kabongo', 'Kalala', 'Kambale', 'Kasongo',
                    'Katembo', 'Kazadi', 'Kitenge', 'Kwete', 'Lemba', 'Londa',
                    'Lubaki', 'Lukusa', 'Lunda', 'Lusamba', 'Lutumba', 'Mabiala',
                    'Mafuta', 'Makasi', 'Malembe', 'Malonda', 'Manda', 'Mangala',
                    'Matondo', 'Mawazo', 'Mayele', 'Mukambilwa', 'Ngoy', 'Nsenga',
                    'Nsiku', 'Nsumbu', 'Ntumba', 'Numbi', 'Nzau', 'Nzita', 'Nzuzi',
                    'Pongo', 'Sefu', 'Shabani', 'Tshilomba', 'Tshimanga', 'Tshomba',
                ],
            ],
            'somali' => [
                'male_first' => [
                    'Abdirahman', 'Abdullahi', 'Ahmed', 'Ali', 'Amin', 'Bashir',
                    'Dahir', 'Farah', 'Hassan', 'Hussein', 'Ibrahim', 'Ismail',
                    'Jama', 'Jibril', 'Khalid', 'Liban', 'Mahad', 'Mahamed',
                    'Mohamud', 'Mukhtar', 'Musa', 'Mustafa', 'Nur', 'Omar',
                    'Osman', 'Qasim', 'Rashid', 'Said', 'Sharif', 'Suleiman',
                    'Warsame', 'Yasin', 'Yusuf', 'Zakariya',
                ],
                'female_first' => [
                    'Aaminah', 'Aisha', 'Ayan', 'Bilan', 'Dahabo', 'Deeqa',
                    'Fadumo', 'Fartun', 'Fatima', 'Halima', 'Hawa', 'Hibo',
                    'Hodan', 'Ikran', 'Ilhan', 'Khadija', 'Kowsar', 'Leyla',
                    'Mariam', 'Maryan', 'Muna', 'Naima', 'Nasra', 'Nimo',
                    'Rahma', 'Ramla', 'Roda', 'Safiya', 'Sahra', 'Samira',
                    'Samsam', 'Shamis', 'Sughra', 'Sumaya', 'Zahra', 'Zaynab',
                ],
                'last' => [
                    'Abdulle', 'Ahmed', 'Ali', 'Arale', 'Barre', 'Dahir', 'Farah',
                    'Gedi', 'Hassan', 'Hersi', 'Hussein', 'Ibrahim', 'Ismail',
                    'Jama', 'Jimale', 'Khalif', 'Libin', 'Mahamud', 'Mohamed',
                    'Mohamud', 'Mohamed', 'Mumin', 'Nur', 'Omar', 'Osman',
                    'Qalif', 'Said', 'Sharif', 'Shire', 'Siad', 'Warsame', 'Yusuf',
                ],
            ],
            'gambian' => [
                'male_first' => [
                    'Abdoulie', 'Alpha', 'Alieu', 'Amadou', 'Baboucarr', 'Bakary',
                    'Bubacarr', 'Cherno', 'Demba', 'Ebrima', 'Essa', 'Foday',
                    'Gibou', 'Habib', 'Ismaila', 'Jallow', 'Kebba', 'Kemo',
                    'Lamin', 'Lansana', 'Madi', 'Malick', 'Momodou', 'Musa',
                    'Mustapha', 'Njaga', 'Omar', 'Ousainou', 'Pa', 'Saidu',
                    'Samba', 'Sanna', 'Sulayman', 'Tijan',
                ],
                'female_first' => [
                    'Adama', 'Aja', 'Aminata', 'Amie', 'Awa', 'Binta', 'Bintou',
                    'Coumba', 'Daboh', 'Fatima', 'Fatou', 'Fatoumata', 'Foday',
                    'Haddy', 'Hawa', 'Jainaba', 'Jarra', 'Jatou', 'Kadijatou',
                    'Kanku', 'Khadijatou', 'Kumba', 'Maimuna', 'Mariama',
                    'Musukuta', 'Nabou', 'Naffie', 'Ndey', 'Nene', 'Njemeh',
                    'Nyimasata', 'Oumie', 'Penda', 'Ramatoulie', 'Saffie',
                    'Sainabou', 'Sarjo', 'Sunkaru', 'Teneng', 'Tida', 'Tulai',
                    'Yassin', 'Yerro',
                ],
                'last' => [
                    'Bah', 'Badjie', 'Baldeh', 'Barrow', 'Bojang', 'Camara',
                    'Ceesay', 'Colley', 'Conteh', 'Darboe', 'Dibba', 'Fadera',
                    'Faye', 'Gassama', 'Gaye', 'Gibba', 'Gomez', 'Jallow',
                    'Jatta', 'Jawo', 'Jeng', 'Joof', 'Kanteh', 'Keita',
                    'Konteh', 'Leigh', 'Maneh', 'Manjang', 'Mendy', 'Minteh',
                    'Mbye', 'Ndiaye', 'Ndow', 'Njie', 'Nyang', 'Saine',
                    'Sambou', 'Sanneh', 'Sanyang', 'Sarr', 'Secka', 'Sewa',
                    'Sillah', 'Sonko', 'Sowe', 'Susso', 'Touray', 'Wally',
                ],
            ],
        ];
    }

    private function universities(): array
    {
        return [
            'Makerere University',
            'Kyambogo University',
            'Uganda Christian University',
            'Islamic University in Uganda',
            'Gulu University',
            'Mbarara University of Science and Technology',
            'Busitema University',
            'Soroti University',
            'Kabale University',
            'Ndejje University',
            'Kampala International University',
            'Mountains of the Moon University',
            'Lira University',
            'Uganda Technology and Management University',
            'St. Lawrence University',
            'Victoria University',
            'International University of East Africa',
            'Cavendish University Uganda',
            'Nkumba University',
            'Bugema University',
        ];
    }

    private function programs(): array
    {
        return [
            'Computer Science',
            'Cybersecurity',
            'Information Technology',
            'Software Engineering',
            'Computer Engineering',
            'Electrical Engineering',
            'Telecommunications Engineering',
            'Data Science',
            'Artificial Intelligence',
            'Business Computing',
            'Information Systems',
            'Mathematics',
            'Statistics',
            'Physics with Computing',
            'Economics',
        ];
    }

    private function faculties(): array
    {
        return [
            'Computing and Informatics',
            'Engineering',
            'Science',
            'Business and Management',
            'Health Sciences',
            'Education',
            'Law',
            'Social Sciences',
        ];
    }

    private function cities(): array
    {
        return [
            'Kampala', 'Jinja', 'Gulu', 'Mbarara', 'Mbale', 'Entebbe',
            'Soroti', 'Arua', 'Lira', 'Fort Portal', 'Masaka', 'Kabale',
            'Hoima', 'Busia', 'Tororo', 'Mukono', 'Luwero', 'Iganga',
            'Kasese', 'Kitgum', 'Kotido', 'Masindi', 'Mityana', 'Mubende',
            'Nebbi', 'Rukungiri', 'Wakiso', 'Adjumani', 'Apac', 'Bundibugyo',
        ];
    }

    private function residences(): array
    {
        return [
            'Hall A', 'Hall B', 'Hall C', 'Hall D', 'Hall E',
            'Hall F', 'North Hall', 'South Hall', 'East Hall',
            'Complex Hall', 'Off-Campus', 'Off-Campus', 'Off-Campus',
            'Livingstone Hall', 'Nkrumah Hall', 'Senegal Hall',
        ];
    }

    private function genderDistribution(): string
    {
        $r = mt_rand(1, 100);
        if ($r <= 60) {
            return 'male';
        }
        if ($r <= 95) {
            return 'female';
        }

        return 'other';
    }

    private function membershipStatus(int $i): string
    {
        if ($i < 2750) {
            return 'active';
        }
        if ($i < 3750) {
            return 'pending';
        }
        if ($i < 4500) {
            return 'alumni';
        }

        return 'inactive';
    }

    private function membershipType(string $status): string
    {
        if ($status === 'alumni') {
            return 'alumni';
        }
        if ($status === 'inactive') {
            return 'inactive';
        }

        return 'active';
    }

    private function computeRank(int $score): string
    {
        if ($score >= 3000) {
            return 'platinum';
        }
        if ($score >= 1500) {
            return 'gold';
        }
        if ($score >= 500) {
            return 'silver';
        }

        return 'bronze';
    }

    private array $usedEmails = [];

    private function generateUserBatch(int $start, int $count): array
    {
        if (empty($this->usedEmails)) {
            $this->usedEmails = DB::table('users')->pluck('email')->flip()->all();
        }

        $nameData = $this->nameData();
        $nationalities = array_keys($nameData);
        $users = [];

        $uniPrograms = $this->programs();
        $uniFaculties = $this->faculties();
        $uniList = $this->universities();
        $cityList = $this->cities();
        $resList = $this->residences();

        for ($i = 0; $i < $count; $i++) {
            $globalIndex = $start + $i;
            $nat = $nationalities[$globalIndex % count($nationalities)];
            $data = $nameData[$nat];

            $gender = $this->genderDistribution();
            $firstNames = $gender === 'male' ? $data['male_first'] : $data['female_first'];
            $firstName = $this->randomElement($firstNames);
            $lastName = $this->randomElement($data['last']);

            $fullName = "{$firstName} {$lastName}";

            $emailBase = $this->emailFromName($fullName);
            $email = $emailBase;
            $suffix = 1;
            while (isset($this->usedEmails[$email])) {
                $email = $this->emailFromName($fullName, $suffix);
                $suffix++;
            }
            $this->usedEmails[$email] = true;

            $status = $this->membershipStatus($globalIndex);
            $type = $this->membershipType($status);
            $isActive = $status === 'active';

            $year = $isActive ? mt_rand(1, 5) : ($status === 'alumni' ? mt_rand(1, 5) : mt_rand(1, 4));
            $score = $isActive
                ? mt_rand(0, 5000)
                : ($status === 'alumni' ? mt_rand(0, 3000) : mt_rand(0, 500));
            $attendanceCount = $isActive
                ? min(mt_rand(0, 50), $score > 1000 ? mt_rand(10, 60) : mt_rand(0, 20))
                : 0;
            $streak = $isActive && $attendanceCount > 10 ? min(mt_rand(0, $attendanceCount), mt_rand(1, 30)) : mt_rand(0, 3);

            $joinedAt = $this->randomDate(
                $this->now->copy()->subMonths(mt_rand(1, 30)),
                $this->now->copy()->subMonth()
            );

            $users[] = [
                'name' => $fullName,
                'email' => $email,
                'email_verified_at' => $status !== 'pending' ? $joinedAt : null,
                'approved_at' => $status !== 'pending' ? $joinedAt : null,
                'password' => $this->password(),
                'remember_token' => Str::random(10),
                'student_id' => 'STU'.str_pad((string) (50000 + $globalIndex), 6, '0', STR_PAD_LEFT),
                'registration_number' => 'REG'.str_pad((string) (20000 + $globalIndex), 6, '0', STR_PAD_LEFT),
                'phone' => '+2567'.str_pad((string) mt_rand(10000000, 99999999), 8, '0', STR_PAD_LEFT),
                'program' => $this->randomElement($uniPrograms),
                'faculty' => $this->randomElement($uniFaculties),
                'year_of_study' => $year,
                'date_of_birth' => $this->randomDate(
                    $this->now->copy()->subYears(30),
                    $this->now->copy()->subYears(18)
                ),
                'gender' => $gender,
                'residence' => $this->randomElement($resList),
                'membership_type' => $type,
                'membership_status' => $status,
                'is_discord_member' => $isActive ? $this->randomBool(80) : $this->randomBool(30),
                'discord_username' => $isActive
                    ? strtolower($firstName.'_'.$lastName).'#'.mt_rand(1000, 9999)
                    : null,
                'joined_at' => $joinedAt,
                'bio' => $isActive
                    ? $this->randomElement([
                        'Cybersecurity enthusiast passionate about ethical hacking.',
                        'Aspiring security professional exploring the world of infosec.',
                        'CTF player and bug bounty hunter in training.',
                        'Network security nerd who loves breaking things to fix them.',
                        'Cryptography enthusiast and puzzle solver.',
                        'Web security researcher learning secure coding practices.',
                        'Digital forensics and incident response enthusiast.',
                        'Python developer diving into security automation.',
                        'Red team wannabe practicing in home lab.',
                        'Blue team defender protecting digital assets.',
                    ])
                    : null,
                'headline' => $isActive
                    ? $this->randomElement([
                        'Aspiring Security Engineer',
                        'CTF Enthusiast',
                        'Ethical Hacker in Training',
                        'Security Researcher',
                        'Network Security Analyst',
                        'Bug Bounty Hunter',
                        'Penetration Tester',
                    ])
                    : null,
                'github_username' => $isActive && $this->randomBool(60)
                    ? strtolower($firstName.'-'.$lastName).mt_rand(1, 999)
                    : null,
                'linkedin_url' => $isActive && $this->randomBool(70)
                    ? 'https://linkedin.com/in/'.strtolower($firstName.'-'.$lastName)
                    : null,
                'personal_website' => $this->randomBool(15) ? 'https://'.strtolower($firstName).'.dev' : null,
                'emergency_contact_name' => $this->randomElement($firstNames).' '.$this->randomElement($data['last']),
                'emergency_contact_phone' => '+2567'.str_pad((string) mt_rand(10000000, 99999999), 8, '0', STR_PAD_LEFT),
                'attendance_count' => $attendanceCount,
                'total_sessions_attended' => $attendanceCount,
                'current_streak' => $streak,
                'longest_streak' => min(mt_rand($streak, $streak + 20), 60),
                'bonus_points' => $isActive ? mt_rand(0, 500) : 0,
                'score' => $score,
                'rank' => $this->computeRank($score),
                'rank_changed_at' => $joinedAt,
                'privacy_settings' => json_encode([
                    'show_email' => false,
                    'show_phone' => false,
                    'show_discord' => true,
                    'show_attendance' => true,
                    'show_profile' => true,
                ]),
                'created_at' => $joinedAt,
                'updated_at' => $joinedAt,
            ];
        }

        return $users;
    }

    private function seedUsers(): void
    {
        $this->command?->info('Creating 5,000 users...');

        $admin = User::firstOrCreate(
            ['email' => 'admin@slau-csic.org'],
            [
                'name' => 'Admin',
                'membership_status' => 'active',
                'membership_type' => 'active',
                'password' => $this->password(),
                'approved_at' => now(),
            ]
        );

        if ($admin->wasRecentlyCreated === false && $admin->approved_at === null) {
            $admin->forceFill(['approved_at' => $admin->joined_at ?? now()])->save();
        }

        $this->adminId = $admin->id;

        $chunkSize = 500;
        $allUserIds = [];

        for ($offset = 0; $offset < 5000; $offset += $chunkSize) {
            $batch = $this->generateUserBatch($offset, $chunkSize);
            DB::table('users')->insert($batch);

            $lastId = DB::getPdo()->lastInsertId();
            $firstId = $lastId - count($batch) + 1;
            for ($i = 0; $i < count($batch); $i++) {
                $allUserIds[] = $firstId + $i;
            }
        }

        $this->userIds = $allUserIds;

        $memberRole = Role::where('name', 'member')->first();
        if ($memberRole) {
            $rolePivotData = array_map(fn ($id) => [
                'role_id' => $memberRole->id,
                'model_type' => User::class,
                'model_id' => $id,
            ], $this->userIds);

            DB::table('model_has_roles')->insert($rolePivotData);
        }

        $this->command?->info('Created '.count($this->userIds).' users with member role.');
    }

    private function seedMemberships(): void
    {
        $this->command?->info('Creating memberships...');

        $users = DB::table('users')->whereIn('id', $this->userIds)->get(['id', 'membership_status', 'membership_type', 'joined_at']);

        $chunks = [];
        foreach ($users as $user) {
            $status = $user->membership_status;
            $chunks[] = [
                'user_id' => $user->id,
                'type' => $user->membership_type,
                'status' => $status,
                'approved_by' => $status !== 'pending' ? $this->adminId : null,
                'approved_at' => $status !== 'pending' ? $this->randomDate($this->now->copy()->subMonths(12), $this->now->copy()->subMonth()) : null,
                'joined_at' => $user->joined_at,
                'created_at' => $user->joined_at,
                'updated_at' => $user->joined_at,
            ];
        }

        foreach (array_chunk($chunks, 500) as $batch) {
            DB::table('memberships')->insert($batch);
        }

        $this->command?->info('Created '.count($chunks).' memberships.');
    }

    private function seedMemberProfiles(): void
    {
        $this->command?->info('Creating member profiles...');

        $users = DB::table('users')->whereIn('id', $this->userIds)->get(['id', 'student_id', 'phone', 'program', 'faculty', 'year_of_study', 'date_of_birth', 'gender', 'residence', 'bio', 'headline', 'emergency_contact_name', 'emergency_contact_phone']);

        $chunks = [];
        foreach ($users as $user) {
            $chunks[] = [
                'user_id' => $user->id,
                'student_id' => $user->student_id,
                'phone' => $user->phone,
                'program' => $user->program,
                'faculty' => $user->faculty,
                'year_of_study' => $user->year_of_study,
                'date_of_birth' => $user->date_of_birth,
                'gender' => $user->gender,
                'residence' => $user->residence,
                'bio' => $user->bio,
                'headline' => $user->headline,
                'emergency_contact_name' => $user->emergency_contact_name,
                'emergency_contact_phone' => $user->emergency_contact_phone,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        foreach (array_chunk($chunks, 500) as $batch) {
            DB::table('member_profiles')->insert($batch);
        }

        $this->command?->info('Created '.count($chunks).' member profiles.');
    }

    private function seedSocialLinks(): void
    {
        $users = DB::table('users')->whereIn('id', $this->userIds)->get(['id', 'github_username', 'linkedin_url', 'discord_username', 'is_discord_member']);

        $chunks = [];
        foreach ($users as $user) {
            $chunks[] = [
                'user_id' => $user->id,
                'github_username' => $user->github_username,
                'linkedin_url' => $user->linkedin_url,
                'discord_username' => $user->discord_username,
                'is_discord_member' => $user->is_discord_member,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        foreach (array_chunk($chunks, 500) as $batch) {
            DB::table('social_links')->insert($batch);
        }

        $this->command?->info('Created '.count($chunks).' social links.');
    }

    private function seedUserPrivacies(): void
    {
        $chunks = array_map(fn ($id) => [
            'user_id' => $id,
            'show_email' => false,
            'show_phone' => false,
            'show_discord' => true,
            'show_attendance' => true,
            'show_program' => true,
            'show_year' => true,
            'show_profile' => true,
            'allow_contact' => $this->randomBool(70),
            'created_at' => now(),
            'updated_at' => now(),
        ], $this->userIds);

        foreach (array_chunk($chunks, 500) as $batch) {
            DB::table('user_privacies')->insert($batch);
        }

        $this->command?->info('Created '.count($chunks).' privacy settings.');
    }

    private function seedNotificationPreferences(): void
    {
        $chunks = array_map(fn ($id) => [
            'user_id' => $id,
            'event_reminders' => $this->randomBool(80),
            'event_cancellations' => $this->randomBool(90),
            'challenge_solved' => $this->randomBool(70),
            'membership_updates' => $this->randomBool(85),
            'broadcast_messages' => $this->randomBool(75),
            'fine_notifications' => $this->randomBool(95),
            'weekly_digest' => $this->randomBool(60),
            'created_at' => now(),
            'updated_at' => now(),
        ], $this->userIds);

        foreach (array_chunk($chunks, 500) as $batch) {
            DB::table('user_notification_preferences')->insert($batch);
        }

        $this->command?->info('Created '.count($chunks).' notification preferences.');
    }

    private function seedGamificationStats(): void
    {
        $users = DB::table('users')->whereIn('id', $this->userIds)->get(['id', 'attendance_count', 'total_sessions_attended', 'current_streak', 'longest_streak', 'bonus_points', 'score', 'rank']);

        $chunks = [];
        foreach ($users as $user) {
            $chunks[] = [
                'user_id' => $user->id,
                'attendance_count' => $user->attendance_count,
                'total_sessions_attended' => $user->total_sessions_attended,
                'current_streak' => $user->current_streak,
                'longest_streak' => $user->longest_streak,
                'bonus_points' => $user->bonus_points,
                'score' => $user->score,
                'rank' => $user->rank,
                'rank_changed_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        foreach (array_chunk($chunks, 500) as $batch) {
            DB::table('gamification_stats')->insert($batch);
        }

        $this->command?->info('Created '.count($chunks).' gamification stats.');
    }

    private function seedEvents(): void
    {
        $this->command?->info('Creating 60 events...');

        $organizerIds = $this->randomElements($this->activeUserIds(50), 20);
        $categories = EventCategory::pluck('id', 'slug');

        $eventDefs = [
            // Past events (Jan 2025 - Jul 2026)
            ['title' => 'Welcome Back Workshop 2025', 'type' => 'workshop', 'past_days' => 540, 'cats' => ['workshop']],
            ['title' => 'Introduction to Linux CLI', 'type' => 'workshop', 'past_days' => 520, 'cats' => ['workshop', 'training']],
            ['title' => 'Web Security Fundamentals', 'type' => 'workshop', 'past_days' => 500, 'cats' => ['workshop']],
            ['title' => 'SLAU CSIC Internal CTF 2025', 'type' => 'ctf', 'past_days' => 480, 'cats' => ['ctf', 'competition']],
            ['title' => 'Network Scanning with Nmap', 'type' => 'workshop', 'past_days' => 460, 'cats' => ['workshop', 'training']],
            ['title' => 'Python for Security Automation', 'type' => 'workshop', 'past_days' => 440, 'cats' => ['workshop']],
            ['title' => 'Annual Cybersecurity Awareness Day', 'type' => 'awareness_campaign', 'past_days' => 420, 'cats' => ['awareness_campaign']],
            ['title' => 'Cryptography Basics Workshop', 'type' => 'workshop', 'past_days' => 400, 'cats' => ['workshop', 'training']],
            ['title' => 'Quarterly CTF Challenge', 'type' => 'ctf', 'past_days' => 380, 'cats' => ['ctf']],
            ['title' => 'Social Engineering Defense', 'type' => 'talk', 'past_days' => 360, 'cats' => ['talk', 'awareness_campaign']],
            ['title' => 'Git & Open Source Contribution', 'type' => 'workshop', 'past_days' => 340, 'cats' => ['workshop']],
            ['title' => 'Capture The Flag Bootcamp', 'type' => 'bootcamp', 'past_days' => 320, 'cats' => ['bootcamp', 'ctf']],
            ['title' => 'Digital Forensics Investigation', 'type' => 'workshop', 'past_days' => 300, 'cats' => ['workshop', 'training']],
            ['title' => 'OWASP Top 10 Deep Dive', 'type' => 'workshop', 'past_days' => 280, 'cats' => ['workshop']],
            ['title' => 'Networking Night: Mixer 2025', 'type' => 'social', 'past_days' => 260, 'cats' => ['social']],
            ['title' => 'Malware Analysis Lab', 'type' => 'workshop', 'past_days' => 240, 'cats' => ['workshop', 'training']],
            ['title' => 'SLAU CSIC Hackathon 2025', 'type' => 'hackathon', 'past_days' => 220, 'cats' => ['hackathon', 'competition']],
            ['title' => 'Cloud Security with AWS', 'type' => 'workshop', 'past_days' => 200, 'cats' => ['workshop', 'training']],
            ['title' => 'Ethical Hacking Career Panel', 'type' => 'talk', 'past_days' => 180, 'cats' => ['talk']],
            ['title' => 'Wireless Security Assessment', 'type' => 'workshop', 'past_days' => 160, 'cats' => ['workshop']],
            ['title' => 'Bug Bounty Methodology', 'type' => 'workshop', 'past_days' => 140, 'cats' => ['workshop']],
            ['title' => 'Women in Tech Networking', 'type' => 'social', 'past_days' => 120, 'cats' => ['social', 'talk']],
            ['title' => 'Reverse Engineering with Ghidra', 'type' => 'workshop', 'past_days' => 100, 'cats' => ['workshop', 'training']],
            ['title' => 'Incident Response Simulation', 'type' => 'workshop', 'past_days' => 80, 'cats' => ['workshop']],
            ['title' => 'Linux Privilege Escalation', 'type' => 'workshop', 'past_days' => 60, 'cats' => ['workshop', 'training']],
            ['title' => 'SLAU CSIC Internal CTF 2026', 'type' => 'ctf', 'past_days' => 45, 'cats' => ['ctf', 'competition']],
            ['title' => 'Buffer Overflow Exploitation', 'type' => 'workshop', 'past_days' => 30, 'cats' => ['workshop']],
            ['title' => 'End of Semester Party', 'type' => 'social', 'past_days' => 14, 'cats' => ['social']],
            ['title' => 'Ransomware Defense Strategies', 'type' => 'talk', 'past_days' => 7, 'cats' => ['talk', 'awareness_campaign']],
            // Current events
            ['title' => 'Advanced Web Exploitation', 'type' => 'workshop', 'future_days' => 3, 'cats' => ['workshop']],
            ['title' => 'Monthly General Meeting - August', 'type' => 'talk', 'future_days' => 5, 'cats' => ['talk', 'meeting']],
            ['title' => 'CEH Exam Prep Bootcamp', 'type' => 'bootcamp', 'future_days' => 10, 'cats' => ['bootcamp', 'training']],
            ['title' => 'Introduction to Malware Analysis', 'type' => 'workshop', 'future_days' => 7, 'cats' => ['workshop', 'training']],
            ['title' => 'Women in Cybersecurity Panel', 'type' => 'talk', 'future_days' => 9, 'cats' => ['talk']],
            // Upcoming events
            ['title' => 'Network Security: Theory to Practice', 'type' => 'workshop', 'future_days' => 17, 'cats' => ['workshop', 'training']],
            ['title' => 'SLAU Internal CTF 2026', 'type' => 'ctf', 'future_days' => 14, 'cats' => ['ctf', 'competition']],
            ['title' => 'Cybersecurity Career Fair 2026', 'type' => 'talk', 'future_days' => 21, 'cats' => ['talk']],
            ['title' => 'Crypto & Privacy: Signal Protocol', 'type' => 'workshop', 'future_days' => 24, 'cats' => ['workshop']],
            ['title' => 'Security Tool Hackathon', 'type' => 'hackathon', 'future_days' => 28, 'cats' => ['hackathon', 'competition']],
            ['title' => 'Cloud Security Essentials: AWS', 'type' => 'workshop', 'future_days' => 12, 'cats' => ['workshop', 'training']],
            ['title' => 'Phishing Defense Workshop', 'type' => 'workshop', 'future_days' => 35, 'cats' => ['workshop', 'awareness_campaign']],
            ['title' => 'End of Year Awareness Campaign', 'type' => 'awareness_campaign', 'future_days' => 35, 'cats' => ['awareness_campaign', 'social']],
            ['title' => 'Python for Red Teaming', 'type' => 'workshop', 'future_days' => 42, 'cats' => ['workshop', 'training']],
            ['title' => 'Quarterly CTF Challenge - Q3', 'type' => 'ctf', 'future_days' => 50, 'cats' => ['ctf']],
            ['title' => 'Docker & Kubernetes Security', 'type' => 'workshop', 'future_days' => 56, 'cats' => ['workshop', 'training']],
            ['title' => 'Annual General Meeting 2026', 'type' => 'talk', 'future_days' => 60, 'cats' => ['talk', 'meeting']],
            ['title' => 'OSINT Investigation Techniques', 'type' => 'workshop', 'future_days' => 63, 'cats' => ['workshop']],
            ['title' => 'Active Directory Exploitation', 'type' => 'workshop', 'future_days' => 70, 'cats' => ['workshop', 'training']],
            ['title' => 'Cybersecurity Trivia Night', 'type' => 'social', 'future_days' => 77, 'cats' => ['social']],
            ['title' => 'IoT Security Fundamentals', 'type' => 'workshop', 'future_days' => 84, 'cats' => ['workshop']],
            ['title' => 'Guest Lecture: CISO Safaricom', 'type' => 'talk', 'future_days' => 90, 'cats' => ['talk']],
            ['title' => 'SLAU CSIC Hackathon 2026', 'type' => 'hackathon', 'future_days' => 95, 'cats' => ['hackathon', 'competition']],
            ['title' => 'Mobile App Security Testing', 'type' => 'workshop', 'future_days' => 100, 'cats' => ['workshop', 'training']],
            ['title' => 'Year-End Awards Ceremony', 'type' => 'social', 'future_days' => 110, 'cats' => ['social', 'talk']],
            ['title' => 'Advanced Cryptography Workshop', 'type' => 'workshop', 'future_days' => 120, 'cats' => ['workshop']],
            ['title' => 'Red Team vs Blue Team Exercise', 'type' => 'hackathon', 'future_days' => 130, 'cats' => ['hackathon', 'competition']],
            ['title' => 'Career Prep: CV & Interview Skills', 'type' => 'talk', 'future_days' => 140, 'cats' => ['talk']],
            ['title' => 'Blockchain Security Workshop', 'type' => 'workshop', 'future_days' => 150, 'cats' => ['workshop']],
        ];

        $insertedIds = [];
        foreach ($eventDefs as $def) {
            $slug = Str::slug($def['title']);

            $loc = $this->randomElement([
                'Cyber Lab 101, Engineering Building',
                'CS Lab 201, Engineering Building',
                'Room 301, Engineering Building',
                'Conference Room A, IT Building',
                'Main Auditorium, Engineering Building',
                'Innovation Hub, Student Center',
                'Network Lab, IT Building',
                'CS Building, 2nd Floor Labs',
                'Student Center Quadrangle',
                'Room 305, IT Building',
            ]);

            if (isset($def['past_days'])) {
                $start = $this->now->copy()->subDays($def['past_days'])->setTime(mt_rand(9, 16), 0, 0);
                $end = (clone $start)->addHours(mt_rand(2, 8));
                $status = 'completed';
                $regDeadline = (clone $start)->subDays(mt_rand(1, 7));
            } else {
                $start = $this->now->copy()->addDays($def['future_days'])->setTime(mt_rand(9, 16), 0, 0);
                $end = (clone $start)->addHours(mt_rand(2, 8));
                $status = 'published';
                $regDeadline = (clone $start)->subDays(mt_rand(1, 5));
            }

            $event = Event::firstOrCreate(['slug' => $slug], [
                'title' => $def['title'],
                'description' => '<p>'.$def['title'].' — a '.$def['type'].' event organized by SLAU CSIC for members to build cybersecurity skills.</p><p>This hands-on session covers practical techniques and real-world scenarios.</p>',
                'type' => $def['type'],
                'start_date' => $start,
                'end_date' => $end,
                'location' => $loc,
                'max_participants' => mt_rand(15, 60),
                'registration_required' => true,
                'waitlist_enabled' => $this->randomBool(60),
                'is_public' => $this->randomBool(80),
                'visibility' => 'members_only',
                'registration_deadline' => $regDeadline,
                'registration_type' => 'first_come',
                'status' => $status,
                'organizer_id' => $this->randomElement($organizerIds),
                'skill_level' => $this->randomElement(['beginner', 'intermediate', 'intermediate', 'advanced']),
                'registration_fee' => $this->randomBool(20) ? mt_rand(100, 500) : 0,
                'created_at' => $start->copy()->subDays(mt_rand(7, 30)),
                'updated_at' => $start->copy()->subDays(mt_rand(1, 7)),
            ]);

            $catIds = collect($def['cats'])
                ->map(fn ($s) => $categories[$s] ?? null)
                ->filter()
                ->values()
                ->toArray();

            if (! empty($catIds)) {
                $event->categories()->sync($catIds);
            }

            $insertedIds[] = $event->id;
        }

        $this->eventIds = $insertedIds;
        $this->command?->info('Created '.count($insertedIds).' events.');
    }

    private function activeUserIds(int $min = 0): array
    {
        if (empty($this->activeUserIds)) {
            $this->activeUserIds = DB::table('users')
                ->whereIn('id', $this->userIds)
                ->where('membership_status', 'active')
                ->pluck('id')
                ->toArray();
        }

        if ($min > 0 && count($this->activeUserIds) < $min) {
            $this->activeUserIds = array_merge(
                $this->activeUserIds,
                $this->randomElements($this->userIds, $min - count($this->activeUserIds))
            );
        }

        return $this->activeUserIds;
    }

    private function seedEventRegistrations(): void
    {
        $this->command?->info('Creating event registrations...');

        $activeIds = $this->activeUserIds();
        $events = Event::whereIn('id', $this->eventIds)->get();

        $total = 0;
        $batch = [];

        foreach ($events as $event) {
            $maxReg = min($event->max_participants ?? 30, count($activeIds));
            $participants = $this->randomElements($activeIds, mt_rand((int) ($maxReg * 0.3), $maxReg));

            foreach ($participants as $uid) {
                $batch[] = [
                    'event_id' => $event->id,
                    'user_id' => $uid,
                    'status' => $this->randomElement(['registered', 'registered', 'attended', 'cancelled']),
                    'rsvp_status' => $this->randomElement(['attending', 'attending', 'attending', 'not_attending', 'maybe']),
                    'registered_at' => $this->randomDate(
                        $event->created_at,
                        $event->registration_deadline ?? $event->start_date
                    ),
                    'payment_completed' => $event->registration_fee > 0 ? $this->randomBool(70) : true,
                    'created_at' => $this->randomDate($event->created_at, $event->start_date),
                    'updated_at' => now(),
                ];

                $total++;

                if (count($batch) >= 500) {
                    DB::table('event_registrations')->insert($batch);
                    $batch = [];
                }
            }
        }

        if (! empty($batch)) {
            DB::table('event_registrations')->insert($batch);
        }

        $this->command?->info("Created {$total} event registrations.");
    }

    private function seedEventAttendance(): void
    {
        $this->command?->info('Creating attendance records...');

        $pastEventIds = Event::whereIn('id', $this->eventIds)
            ->where('start_date', '<', $this->now)
            ->pluck('id')
            ->toArray();

        $registrations = DB::table('event_registrations')
            ->whereIn('event_id', $pastEventIds)
            ->whereIn('status', ['registered', 'attended'])
            ->get();

        $total = 0;
        $batch = [];

        foreach ($registrations as $reg) {
            if (! $this->randomBool(65)) {
                continue;
            }

            $event = Event::find($reg->event_id);

            $batch[] = [
                'event_id' => $reg->event_id,
                'member_id' => $reg->user_id,
                'status' => $this->randomElement(['present', 'present', 'present', 'late', 'excused']),
                'checked_in_at' => $event ? $this->randomDate($event->start_date, $event->end_date ?? (clone $event->start_date)->addHours(4)) : $this->now->copy()->subDays(mt_rand(1, 30)),
                'recorded_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $total++;

            if (count($batch) >= 500) {
                DB::table('event_attendance')->insert($batch);
                $batch = [];
            }
        }

        if (! empty($batch)) {
            DB::table('event_attendance')->insert($batch);
        }

        $this->command?->info("Created {$total} attendance records.");
    }

    private function seedEventFeedback(): void
    {
        $this->command?->info('Creating event feedback...');

        $pastAttendance = DB::table('event_attendance')
            ->whereIn('status', ['present', 'late'])
            ->inRandomOrder()
            ->limit(1000)
            ->get();

        $total = 0;
        $batch = [];

        foreach ($pastAttendance as $att) {
            if (! $this->randomBool(50)) {
                continue;
            }

            $batch[] = [
                'event_id' => $att->event_id,
                'user_id' => $att->member_id,
                'rating' => mt_rand(3, 5),
                'content_quality' => mt_rand(3, 5),
                'instructor_rating' => mt_rand(3, 5),
                'pace_rating' => mt_rand(2, 5),
                'feedback_text' => $this->randomElement([
                    'Great workshop, learned a lot!',
                    'Very practical and hands-on.',
                    'The instructor was knowledgeable.',
                    'Would love more advanced sessions.',
                    'Good pace for beginners.',
                    'Excellent content and delivery.',
                    'Needs more lab exercises.',
                    'Perfect introduction to the topic.',
                    'Well organized and engaging.',
                    'The materials were very helpful.',
                ]),
                'is_anonymous' => $this->randomBool(30),
                'created_at' => $this->randomDate($this->now->copy()->subYear(), $this->now),
                'updated_at' => now(),
            ];

            $total++;

            if (count($batch) >= 500) {
                DB::table('event_feedback')->insert($batch);
                $batch = [];
            }
        }

        if (! empty($batch)) {
            DB::table('event_feedback')->insert($batch);
        }

        $this->command?->info("Created {$total} feedback records.");
    }

    private function seedMeetings(): void
    {
        $this->command?->info('Creating 48 meetings...');

        $activeIds = $this->activeUserIds();

        $titleTemplates = [
            'general' => ['Weekly General Meeting', 'Monthly General Meeting', 'Members Assembly'],
            'executive' => ['Executive Board Meeting', 'Board Strategy Session', 'Leadership Sync'],
            'training' => ['Python for Security Training', 'Linux Basics Training', 'Network Fundamentals', 'CTF Preparation Training'],
            'workshop' => ['Web Security Workshop', 'Crypto Workshop', 'Forensics Workshop', 'OSINT Workshop'],
            'special' => ['Guest Speaker Session', 'Industry Talk', 'Project Demo Day', 'Hackathon Planning'],
        ];

        $meetings = [];

        for ($i = 0; $i < 48; $i++) {
            $daysAgo = mt_rand(0, 720);
            $scheduledAt = $this->now->copy()->subDays($daysAgo)->setTime(mt_rand(14, 18), mt_rand(0, 59), 0);
            $duration = $this->randomElement([60, 90, 120]);

            $type = $this->randomElement(array_keys($titleTemplates));
            $title = $this->randomElement($titleTemplates[$type]);

            if ($daysAgo > 0) {
                $startedAt = (clone $scheduledAt)->addMinutes(mt_rand(0, 10));
                $endedAt = (clone $startedAt)->addMinutes($duration);
            } else {
                $startedAt = null;
                $endedAt = null;
            }

            $attendanceOpen = $daysAgo === 0;

            $meeting = Meeting::create([
                'title' => $title.' #'.($i + 1),
                'description' => '<p>'.$title.' session for SLAU CSIC members.</p>',
                'type' => $type,
                'scheduled_at' => $scheduledAt,
                'started_at' => $startedAt,
                'ended_at' => $endedAt,
                'location' => $this->randomElement([
                    'Room 301, Engineering Building',
                    'Conference Room A',
                    'CS Lab 101',
                    'Cyber Lab 101',
                    'Online - Google Meet',
                    'IT Building Lab 3',
                    'Student Center Room 5',
                ]),
                'duration_minutes' => $duration,
                'expected_attendees' => mt_rand(10, 50),
                'late_threshold_minutes' => 15,
                'attendance_open' => $attendanceOpen,
                'created_by' => $this->adminId,
                'agenda' => '<ol><li>Opening remarks</li><li>Review of previous session</li><li>Main discussion</li><li>Q&A</li><li>Closing remarks</li></ol>',
                'minutes_status' => $daysAgo > 0 ? 'published' : 'draft',
                'meeting_code' => strtoupper(Str::random(8)),
            ]);

            $this->meetingIds[] = $meeting->id;

            if ($daysAgo > 0) {
                $attendeeCount = mt_rand(5, min($meeting->expected_attendees, count($activeIds)));
                $attendees = $this->randomElements($activeIds, $attendeeCount);

                $attBatch = [];
                foreach ($attendees as $uid) {
                    $attBatch[] = [
                        'meeting_id' => $meeting->id,
                        'user_id' => $uid,
                        'status' => $this->randomElement(['present', 'present', 'present', 'late', 'absent']),
                        'checked_in_at' => $this->randomDate(
                            $meeting->started_at ?? $meeting->scheduled_at,
                            ($meeting->started_at ?? $meeting->scheduled_at)->addMinutes(30)
                        ),
                        'check_in_method' => $this->randomElement(['qr_code', 'qr_code', 'manual', 'admin_override']),
                        'created_at' => $meeting->started_at ?? $meeting->scheduled_at,
                        'updated_at' => now(),
                    ];

                    if (count($attBatch) >= 500) {
                        DB::table('attendance')->insert($attBatch);
                        $attBatch = [];
                    }
                }

                if (! empty($attBatch)) {
                    DB::table('attendance')->insert($attBatch);
                }
            }
        }

        $this->command?->info('Created '.count($this->meetingIds).' meetings.');
    }

    private function seedCtfCompetitions(): void
    {
        $this->command?->info('Creating CTF competitions...');

        $activeIds = $this->activeUserIds();

        $catSlugs = [
            'web' => \App\Models\CtfCategory::firstOrCreate(
                ['slug' => 'web'], ['name' => 'Web', 'color' => '#3b82f6', 'icon' => '🌐', 'sort_order' => 0]
            )->id,
            'crypto' => \App\Models\CtfCategory::firstOrCreate(
                ['slug' => 'crypto'], ['name' => 'Crypto', 'color' => '#8b5cf6', 'icon' => '🔐', 'sort_order' => 1]
            )->id,
            'forensics' => \App\Models\CtfCategory::firstOrCreate(
                ['slug' => 'forensics'], ['name' => 'Forensics', 'color' => '#ef4444', 'icon' => '🔍', 'sort_order' => 2]
            )->id,
            'binary' => \App\Models\CtfCategory::firstOrCreate(
                ['slug' => 'binary'], ['name' => 'Binary Exploitation', 'color' => '#f59e0b', 'icon' => '💻', 'sort_order' => 3]
            )->id,
            'misc' => \App\Models\CtfCategory::firstOrCreate(
                ['slug' => 'misc'], ['name' => 'Miscellaneous', 'color' => '#06b6d4', 'icon' => '📌', 'sort_order' => 6]
            )->id,
        ];

        $catMap = ['web' => 'web', 'crypto' => 'crypto', 'forensics' => 'forensics', 'binary' => 'binary', 'misc' => 'misc'];

        $competitions = [
            [
                'title' => 'SLAU CSIC Beginner CTF 2025',
                'past_days' => 400,
                'challenges' => [
                    ['title' => 'Welcome Flag', 'points' => 10, 'difficulty' => 'easy', 'cat' => 'web', 'flag' => 'SLAU_CSIC{w3lc0m3}'],
                    ['title' => 'Base64 Basics', 'points' => 20, 'difficulty' => 'easy', 'cat' => 'web', 'flag' => 'SLAU_CSIC{b4s3_64}'],
                    ['title' => 'Caesar Cipher', 'points' => 15, 'difficulty' => 'easy', 'cat' => 'crypto', 'flag' => 'SLAU_CSIC{c43s4r}'],
                    ['title' => 'Hidden in Metadata', 'points' => 25, 'difficulty' => 'easy', 'cat' => 'forensics', 'flag' => 'SLAU_CSIC{m3t4}'],
                    ['title' => 'SQL Injection 101', 'points' => 30, 'difficulty' => 'medium', 'cat' => 'web', 'flag' => 'SLAU_CSIC{sq1_1nj3ct}'],
                ],
            ],
            [
                'title' => 'SLAU CSIC Spring CTF 2025',
                'past_days' => 250,
                'challenges' => [
                    ['title' => 'XSS Challenge', 'points' => 30, 'difficulty' => 'medium', 'cat' => 'web', 'flag' => 'SLAU_CSIC{xss_m3}'],
                    ['title' => 'Hash Cracking', 'points' => 25, 'difficulty' => 'easy', 'cat' => 'crypto', 'flag' => 'SLAU_CSIC{h45h3d}'],
                    ['title' => 'Steganography', 'points' => 35, 'difficulty' => 'medium', 'cat' => 'forensics', 'flag' => 'SLAU_CSIC{st3g0}'],
                    ['title' => 'Binary Exploitation', 'points' => 50, 'difficulty' => 'hard', 'cat' => 'binary', 'flag' => 'SLAU_CSIC{b0f}'],
                    ['title' => 'Network Forensics', 'points' => 40, 'difficulty' => 'medium', 'cat' => 'forensics', 'flag' => 'SLAU_CSIC{p4ck3t}'],
                ],
            ],
            [
                'title' => 'SLAU CSIC Summer CTF 2025',
                'past_days' => 120,
                'challenges' => [
                    ['title' => 'JWT Forgery', 'points' => 40, 'difficulty' => 'medium', 'cat' => 'web', 'flag' => 'SLAU_CSIC{jwt_f0rg3}'],
                    ['title' => 'RSA Decryption', 'points' => 50, 'difficulty' => 'hard', 'cat' => 'crypto', 'flag' => 'SLAU_CSIC{rs4_br34k}'],
                    ['title' => 'Memory Dump Analysis', 'points' => 45, 'difficulty' => 'hard', 'cat' => 'forensics', 'flag' => 'SLAU_CSIC{m3m0ry}'],
                    ['title' => 'SSRF Exploitation', 'points' => 35, 'difficulty' => 'medium', 'cat' => 'web', 'flag' => 'SLAU_CSIC{ssrf_m3}'],
                    ['title' => 'Zigzag Cipher', 'points' => 20, 'difficulty' => 'easy', 'cat' => 'crypto', 'flag' => 'SLAU_CSIC{z1gz4g}'],
                ],
            ],
            [
                'title' => 'SLAU CSIC Internal CTF 2026',
                'past_days' => 45,
                'challenges' => [
                    ['title' => 'Command Injection', 'points' => 35, 'difficulty' => 'medium', 'cat' => 'web', 'flag' => 'SLAU_CSIC{cmnd_1nj}'],
                    ['title' => 'OTP Cracking', 'points' => 30, 'difficulty' => 'medium', 'cat' => 'crypto', 'flag' => 'SLAU_CSIC{0tp_cr4ck}'],
                    ['title' => 'Packet Analysis', 'points' => 25, 'difficulty' => 'easy', 'cat' => 'forensics', 'flag' => 'SLAU_CSIC{p4ck3t_4n4l}'],
                    ['title' => 'Format String Vuln', 'points' => 55, 'difficulty' => 'hard', 'cat' => 'binary', 'flag' => 'SLAU_CSIC{f0rm4t_str}'],
                    ['title' => 'OSINT Challenge', 'points' => 20, 'difficulty' => 'easy', 'cat' => 'misc', 'flag' => 'SLAU_CSIC{0s1nt_m3}'],
                ],
            ],
            [
                'title' => 'SLAU CSIC Fall CTF 2026',
                'future_days' => 50,
                'challenges' => [
                    ['title' => 'GraphQL Injection', 'points' => 45, 'difficulty' => 'hard', 'cat' => 'web', 'flag' => 'SLAU_CSIC{gr4ph_ql}'],
                    ['title' => 'Differential Cryptanalysis', 'points' => 60, 'difficulty' => 'hard', 'cat' => 'crypto', 'flag' => 'SLAU_CSIC{d1ff_crypt}'],
                    ['title' => 'Registry Forensics', 'points' => 35, 'difficulty' => 'medium', 'cat' => 'forensics', 'flag' => 'SLAU_CSIC{r3g1stry}'],
                    ['title' => 'Heap Exploitation', 'points' => 70, 'difficulty' => 'hard', 'cat' => 'binary', 'flag' => 'SLAU_CSIC{h34p_exp}'],
                    ['title' => 'Misc: Puzzle', 'points' => 15, 'difficulty' => 'easy', 'cat' => 'misc', 'flag' => 'SLAU_CSIC{puzzl3}'],
                ],
            ],
            [
                'title' => 'SLAU CSIC Year-End CTF 2026',
                'future_days' => 130,
                'challenges' => [
                    ['title' => 'Race Condition', 'points' => 50, 'difficulty' => 'hard', 'cat' => 'web', 'flag' => 'SLAU_CSIC{r4c3_c0nd}'],
                    ['title' => 'Lattice Cryptography', 'points' => 65, 'difficulty' => 'hard', 'cat' => 'crypto', 'flag' => 'SLAU_CSIC{l4tt1c3}'],
                    ['title' => 'Mobile Forensics', 'points' => 40, 'difficulty' => 'medium', 'cat' => 'forensics', 'flag' => 'SLAU_CSIC{m0b1l3_4n4l}'],
                    ['title' => 'Return Oriented Prog', 'points' => 75, 'difficulty' => 'hard', 'cat' => 'binary', 'flag' => 'SLAU_CSIC{r0p_chn}'],
                    ['title' => 'Crypto Puzzle', 'points' => 25, 'difficulty' => 'easy', 'cat' => 'crypto', 'flag' => 'SLAU_CSIC{cr1pt0_puzz}'],
                ],
            ],
        ];

        $seenSlugs = [];
        foreach ($competitions as $compDef) {
            $slug = Str::slug($compDef['title']);
            $origSlug = $slug;
            $n = 2;
            while (in_array($slug, $seenSlugs)) {
                $slug = $origSlug.'-'.$n++;
            }
            $seenSlugs[] = $slug;

            if (isset($compDef['past_days'])) {
                $start = $this->now->copy()->subDays($compDef['past_days']);
                $end = (clone $start)->addDays(mt_rand(3, 7));
                $status = 'completed';
            } else {
                $start = $this->now->copy()->addDays($compDef['future_days']);
                $end = (clone $start)->addDays(mt_rand(3, 7));
                $status = 'published';
            }

            $comp = \App\Models\CtfCompetition::firstOrCreate(['slug' => $slug], [
                'title' => $compDef['title'],
                'description' => $compDef['title'].' — a Capture The Flag competition organized by SLAU CSIC.',
                'start_date' => $start,
                'end_date' => $end,
                'status' => $status,
                'is_public' => true,
                'allow_teams' => false,
                'max_score' => collect($compDef['challenges'])->sum('points'),
            ]);

            $challengeModels = [];
            foreach ($compDef['challenges'] as $cDef) {
                $chSlug = Str::slug($cDef['title']);
                $ch = $comp->challenges()->create([
                    'ctf_category_id' => $catSlugs[$catMap[$cDef['cat']]],
                    'title' => $cDef['title'],
                    'slug' => $chSlug,
                    'description' => 'Solve the '.$cDef['title'].' challenge to capture the flag!',
                    'flag_hash' => hash('sha256', strtolower($cDef['flag'])),
                    'points' => $cDef['points'],
                    'difficulty' => $cDef['difficulty'],
                    'is_active' => true,
                    'sort_order' => $cDef['points'],
                    'solve_count' => 0,
                ]);

                $ch->hints()->create([
                    'tier' => 0,
                    'content' => 'Keep trying! The flag is in format SLAU_CSIC{...}',
                    'cost' => max(1, (int) ($cDef['points'] * 0.1)),
                ]);

                $challengeModels[] = ['model' => $ch, 'def' => $cDef];
            }

            if (isset($compDef['past_days']) && ! empty($activeIds)) {
                $subBatch = [];
                $solveBatch = [];

                foreach ($challengeModels as $entry) {
                    $chId = $entry['model']->id;
                    $chDef = $entry['def'];

                    $solvers = $this->randomElements($activeIds, min(mt_rand(3, 20), count($activeIds)));
                    foreach ($solvers as $uid) {
                        $isCorrect = $this->randomBool(70);
                        $flag = '';
                        $pts = 0;

                        if ($isCorrect) {
                            $flag = $chDef['flag'];
                            $pts = $chDef['points'];
                        }

                        $subBatch[] = [
                            'ctf_challenge_id' => $chId,
                            'user_id' => $uid,
                            'submitted_flag' => $isCorrect ? $flag : 'SLAU_CSIC{wrong_attempt}',
                            'is_correct' => $isCorrect,
                            'points_awarded' => $pts,
                            'attempt_number' => mt_rand(1, 3),
                            'submitted_at' => $this->randomDate($start, $end),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];

                        if ($isCorrect) {
                            $solveBatch[] = [
                                'ctf_challenge_id' => $chId,
                                'user_id' => $uid,
                                'solve_order' => 1,
                                'points_awarded' => $pts,
                                'solved_at' => $this->randomDate($start, $end),
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }

                        if (count($subBatch) >= 500) {
                            DB::table('ctf_submissions')->insert($subBatch);
                            $subBatch = [];
                        }
                        if (count($solveBatch) >= 500) {
                            DB::table('ctf_challenge_solves')->insert($solveBatch);
                            $solveBatch = [];
                        }
                    }
                }

                if (! empty($subBatch)) {
                    DB::table('ctf_submissions')->insert($subBatch);
                }
                if (! empty($solveBatch)) {
                    DB::table('ctf_challenge_solves')->insert($solveBatch);
                }
            }
        }

        $this->command?->info('Created '.count($competitions).' CTF competitions with challenges.');
    }

    private function seedPolls(): void
    {
        $this->command?->info('Creating polls...');

        $activeIds = $this->activeUserIds();

        $pollDefs = [
            ['q' => 'What topic should we cover next?', 'opts' => ['Network Pentesting', 'Web Security', 'Malware Analysis', 'Cloud Security'], 'expire_days' => 10],
            ['q' => 'Best day for weekly meetings?', 'opts' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'], 'expire_days' => 15],
            ['q' => 'Organize a CTF this semester?', 'opts' => ['Yes!', 'No'], 'expire_days' => 7],
            ['q' => 'Preferred programming language for workshops', 'opts' => ['Python', 'JavaScript', 'Go', 'Rust'], 'expire_days' => 20],
            ['q' => 'Venue for annual conference', 'opts' => ['Main Auditorium', 'CSIC Lab Block C', 'Online'], 'expire_days' => -5],
            ['q' => 'Should we have a hackathon?', 'opts' => ['Yes, 48-hour', 'Yes, 24-hour', 'No'], 'expire_days' => 14],
            ['q' => 'What certification should we prep for?', 'opts' => ['CEH', 'OSCP', 'Security+', 'CISSP'], 'expire_days' => -3],
            ['q' => 'Host a guest lecture series?', 'opts' => ['Yes, monthly', 'Yes, quarterly', 'No'], 'expire_days' => 30],
            ['q' => 'Best format for CTF practice', 'opts' => ['Jeopardy', 'Attack-Defense', 'Mixed'], 'expire_days' => -10],
            ['q' => 'Which framework for web dev workshop?', 'opts' => ['Laravel', 'Django', 'Express.js', 'Spring Boot'], 'expire_days' => 25],
            ['q' => 'Membership fee review', 'opts' => ['Keep current', 'Increase', 'Decrease'], 'expire_days' => 60],
            ['q' => 'Focus area for next bootcamp', 'opts' => ['Red Teaming', 'Blue Teaming', 'Cloud Security', 'AppSec'], 'expire_days' => 45],
            ['q' => 'Online vs in-person meetings?', 'opts' => ['In-person', 'Online', 'Hybrid'], 'expire_days' => 90],
            ['q' => 'Should we start a bug bounty program?', 'opts' => ['Yes, internal', 'Yes, public', 'Not yet'], 'expire_days' => -20],
            ['q' => 'Best time for weekend workshops', 'opts' => ['Saturday AM', 'Saturday PM', 'Sunday AM', 'Sunday PM'], 'expire_days' => 35],
        ];

        $pollIds = [];
        foreach ($pollDefs as $def) {
            $expires = $def['expire_days'] > 0
                ? $this->now->copy()->addDays($def['expire_days'])
                : $this->now->copy()->addDays($def['expire_days']);

            $poll = Poll::firstOrCreate(['slug' => Str::slug($def['q'])], [
                'question' => $def['q'],
                'description' => 'Cast your vote for this club decision.',
                'created_by' => $this->adminId,
                'is_published' => true,
                'allow_multiple' => $this->randomBool(20),
                'expires_at' => $expires,
                'votes_count' => 0,
            ]);

            foreach ($def['opts'] as $i => $label) {
                PollOption::create([
                    'poll_id' => $poll->id,
                    'label' => $label,
                    'sort_order' => $i,
                    'votes_count' => 0,
                ]);
            }

            $pollIds[] = $poll->id;
        }

        $options = \App\Models\PollOption::whereIn('poll_id', $pollIds)->get()->groupBy('poll_id');
        foreach ($pollIds as $pid) {
            $voters = $this->randomElements($activeIds, min(mt_rand(10, 100), count($activeIds)));
            $pollOptions = $options[$pid] ?? collect();

            $vBatch = [];
            foreach ($voters as $uid) {
                $opt = $pollOptions->random();
                $vBatch[] = [
                    'poll_id' => $pid,
                    'option_id' => $opt->id,
                    'user_id' => $uid,
                    'created_at' => $this->randomDate($this->now->copy()->subMonths(6), $this->now),
                ];

                if (count($vBatch) >= 500) {
                    DB::table('poll_votes')->insert($vBatch);
                    $vBatch = [];
                }
            }

            if (! empty($vBatch)) {
                DB::table('poll_votes')->insert($vBatch);
            }
        }

        $this->command?->info('Created '.count($pollIds).' polls.');
    }

    private function seedElections(): void
    {
        $this->command?->info('Creating elections...');

        $activeIds = $this->activeUserIds();

        // Closed election (2025)
        $closed = Election::firstOrCreate(['slug' => '2025-cabinet-elections'], [
            'title' => '2025 Cabinet Elections',
            'position' => 'President',
            'description' => 'The previous cabinet election. Results are displayed for reference.',
            'status' => 'closed',
            'starts_at' => $this->now->copy()->subYear()->subDays(7),
            'ends_at' => $this->now->copy()->subYear(),
            'results_visible' => true,
            'results_publish_at' => $this->now->copy()->subYear(),
        ]);

        $closedCandidates = [];
        foreach ([$this->randomElement($activeIds), $this->randomElement($activeIds), $this->randomElement($activeIds)] as $i => $uid) {
            $user = User::find($uid);
            $closedCandidates[] = $closed->candidates()->create([
                'user_id' => $uid,
                'name' => $user?->name ?? 'Candidate '.($i + 1),
                'manifesto' => 'A vision for a stronger cybersecurity club.',
                'agenda' => 'Workshops, CTFs, industry partnerships',
                'sort_order' => $i,
            ]);
        }

        $batchVotes = [];
        foreach ($activeIds as $uid) {
            if ($this->randomBool(30)) {
                $candidate = $this->randomElement($closedCandidates);
                $batchVotes[] = [
                    'election_id' => $closed->id,
                    'election_candidate_id' => $candidate->id,
                    'user_id' => $uid,
                    'receipt_code' => Str::random(20),
                    'receipt_token' => '$2y$12$'.Str::random(53),
                    'created_at' => $this->randomDate($closed->starts_at, $closed->ends_at),
                ];

                if (count($batchVotes) >= 50) {
                    DB::table('election_votes')->insert($batchVotes);
                    $batchVotes = [];
                }
            }
        }

        if ($batchVotes !== []) {
            DB::table('election_votes')->insert($batchVotes);
        }

        // Open election (2026)
        $open = Election::firstOrCreate(['slug' => '2026-cabinet-elections'], [
            'title' => '2026 Cabinet Elections',
            'position' => 'President',
            'description' => 'Cast your vote for the next SLAU CSIC President.',
            'status' => 'open',
            'starts_at' => $this->now->copy()->subDays(7),
            'ends_at' => $this->now->copy()->addDays(7),
            'results_visible' => false,
            'applications_starts_at' => $this->now->copy()->subDays(21),
            'applications_ends_at' => $this->now->copy()->subDays(1),
        ]);

        foreach (['Alice Kamau', 'Bob Ochieng', 'Carol Wanjiku'] as $i => $name) {
            $open->candidates()->create([
                'user_id' => $this->randomElement($activeIds),
                'name' => $name,
                'manifesto' => 'Bringing innovation through collaboration.',
                'agenda' => 'Monthly workshops, industry partnerships, CTF bootcamps',
                'sort_order' => $i,
            ]);
        }

        // Draft election (upcoming VP)
        $draft = Election::firstOrCreate(['slug' => 'upcoming-vice-president-election'], [
            'title' => 'Upcoming Vice President Election',
            'position' => 'Vice President',
            'description' => 'Nominations and campaigning are currently in progress.',
            'status' => 'draft',
            'starts_at' => $this->now->copy()->addDays(14),
            'ends_at' => $this->now->copy()->addDays(28),
            'results_visible' => false,
            'applications_starts_at' => $this->now->copy()->subDays(3),
            'applications_ends_at' => $this->now->copy()->addDays(7),
        ]);

        $this->command?->info('Created 3 elections.');
    }

    private function seedExams(): void
    {
        $this->command?->info('Creating exams...');

        $activeIds = $this->activeUserIds();

        $examDefs = [
            ['title' => 'Cybersecurity Fundamentals', 'duration' => 60, 'passing' => 50],
            ['title' => 'Network Security Basics', 'duration' => 45, 'passing' => 60],
            ['title' => 'Ethical Hacking Principles', 'duration' => 90, 'passing' => 70],
            ['title' => 'Cryptography Concepts', 'duration' => 60, 'passing' => 50],
            ['title' => 'Web Application Security', 'duration' => 60, 'passing' => 60],
            ['title' => 'Incident Response & Forensics', 'duration' => 90, 'passing' => 50],
            ['title' => 'Cloud Security Architecture', 'duration' => 60, 'passing' => 70],
            ['title' => 'SOC Analyst Skills Test', 'duration' => 120, 'passing' => 60],
        ];

        foreach ($examDefs as $def) {
            $exam = Exam::create([
                'user_id' => $this->adminId,
                'title' => $def['title'],
                'description' => 'Assessment covering '.$def['title'].'.',
                'duration_minutes' => $def['duration'],
                'passing_score' => $def['passing'],
                'status' => 'published',
            ]);

            $qCount = mt_rand(3, 6);
            $qbIds = [];
            $eqIds = [];
            $qbRows = [];
            $optRows = [];
            $eqRows = [];
            $now = $this->now;

            for ($i = 0; $i < $qCount; $i++) {
                $qCreated = $now->copy()->subDays(mt_rand(0, 30));
                $qbRows[] = [
                    'user_id' => $this->adminId,
                    'type' => 'multiple_choice',
                    'question_text' => 'Question '.($i + 1).' for '.$def['title'].'?',
                    'marks' => 10,
                    'explanation' => 'This is the explanation for question '.($i + 1).'.',
                    'created_at' => $qCreated,
                    'updated_at' => $qCreated,
                ];
            }

            DB::table('question_bank_questions')->insert($qbRows);
            $firstQbId = DB::getPdo()->lastInsertId() - count($qbRows) + 1;

            for ($i = 0; $i < $qCount; $i++) {
                $qbId = $firstQbId + $i;
                $qbIds[] = $qbId;
                $correctIdx = mt_rand(0, 3);
                foreach (['Option A', 'Option B', 'Option C', 'Option D'] as $j => $optText) {
                    $optRows[] = [
                        'question_id' => $qbId,
                        'option_text' => $optText,
                        'is_correct' => $j === $correctIdx,
                        'order' => $j,
                    ];
                }

                $eqRows[] = [
                    'exam_id' => $exam->id,
                    'question_bank_question_id' => $qbId,
                    'order' => $i,
                    'custom_marks' => null,
                ];
            }

            DB::table('question_bank_options')->insert($optRows);
            $firstOptId = DB::getPdo()->lastInsertId() - count($optRows) + 1;
            DB::table('exam_questions')->insert($eqRows);
            $firstEqId = DB::getPdo()->lastInsertId() - count($eqRows) + 1;

            for ($i = 0; $i < $qCount; $i++) {
                $eqIds[] = $firstEqId + $i;
            }

            $takers = $this->randomElements($activeIds, min(mt_rand(5, 25), count($activeIds)));
            $attemptRows = [];
            $answerRows = [];

            foreach ($takers as $uid) {
                $startedAt = $this->randomDate($now->copy()->subMonths(6), $now);
                $submittedAt = (clone $startedAt)->addMinutes(mt_rand(10, min(60, $def['duration'])));
                $totalScore = mt_rand(0, 100);
                $passed = $totalScore >= $def['passing'];

                $attemptRows[] = [
                    'exam_id' => $exam->id,
                    'user_id' => $uid,
                    'started_at' => $startedAt,
                    'submitted_at' => $submittedAt,
                    'time_remaining_seconds' => mt_rand(0, 3600),
                    'total_score' => $totalScore,
                    'passed' => $passed,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            DB::table('exam_attempts')->insert($attemptRows);
            $firstAttId = DB::getPdo()->lastInsertId() - count($attemptRows) + 1;

            foreach ($attemptRows as $ai => $aRow) {
                $attId = $firstAttId + $ai;
                foreach ($eqIds as $qi => $eqId) {
                    $optBase = $firstOptId + ($qi * 4);
                    $selectedIdx = mt_rand(0, 3);
                    $selectedOptId = $optBase + $selectedIdx;
                    $isCorrect = $selectedIdx === 0;
                    $answerRows[] = [
                        'exam_attempt_id' => $attId,
                        'exam_question_id' => $eqId,
                        'answer_text' => null,
                        'selected_option_id' => $selectedOptId,
                        'is_correct' => $isCorrect,
                        'marks_awarded' => $isCorrect ? 10 : 0,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            if ($answerRows !== []) {
                DB::table('exam_answers')->insert($answerRows);
            }
        }

        $this->command?->info('Created '.count($examDefs).' exams with attempts.');
    }

    private function seedFines(): void
    {
        $this->command?->info('Creating fines...');

        $fineTypeIds = \App\Models\FineType::pluck('id')->toArray();
        $activeIds = $this->activeUserIds();

        $fines = [];
        for ($i = 0; $i < 100; $i++) {
            $isOverdue = $this->randomBool(30);
            $status = $isOverdue ? 'pending' : $this->randomElement(['paid', 'paid', 'waived', 'pending']);
            $amount = $this->randomElement([5000, 10000, 3000, 15000, 2000]);
            $amountPaid = $status === 'paid' ? $amount : ($status === 'partially_paid' ? (int) ($amount * $this->randomFloat(0.1, 0.9)) : 0);

            $fines[] = [
                'user_id' => $this->randomElement($activeIds),
                'fine_type_id' => $this->randomElement($fineTypeIds),
                'amount' => $amount,
                'reason' => $this->randomElement([
                    'Missed club meeting without notice',
                    'Late submission of project',
                    'Event no-show',
                    'Lab violation',
                    'Overdue library book',
                    'Unreturned equipment',
                    'Missed committee meeting',
                ]),
                'issue_date' => $this->randomDate($this->now->copy()->subMonths(6), $this->now),
                'due_date' => $isOverdue
                    ? $this->randomDate($this->now->copy()->subMonths(2), $this->now->copy()->subDay())
                    : $this->randomDate($this->now->copy()->addDays(1), $this->now->copy()->addDays(14)),
                'status' => $status,
                'amount_paid' => $amountPaid,
                'issued_by' => $this->adminId,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        foreach (array_chunk($fines, 100) as $batch) {
            DB::table('fines')->insert($batch);
        }

        $this->command?->info('Created '.count($fines).' fines.');
    }

    private function seedClubResourceProgress(): void
    {
        $this->command?->info('Creating club resource progress...');

        $resIds = DB::table('club_resources')->where('category', 'learning')->pluck('id')->toArray();
        if ($resIds === []) {
            return;
        }

        $activeIds = $this->activeUserIds();
        $now = $this->now;
        $rows = [];

        foreach ($resIds as $resId) {
            $participants = $this->randomElements($activeIds, min(mt_rand(100, 500), count($activeIds)));
            foreach ($participants as $uid) {
                $score = mt_rand(0, 300);
                $completed = mt_rand(0, min(10, $score > 100 ? 8 : 3));
                $rows[] = [
                    'club_resource_id' => $resId,
                    'user_id' => $uid,
                    'status' => $score > 0 ? (mt_rand(1, 3) > 1 ? 'in_progress' : 'completed') : 'not_started',
                    'progress_percentage' => min(100, mt_rand(0, 100)),
                    'completed_units' => $completed,
                    'score' => $score,
                    'last_activity_at' => $this->randomDate($now->copy()->subMonths(3), $now),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if ($rows !== []) {
            DB::table('club_resource_progress')->insert($rows);
        }

        $this->command?->info('Created '.count($rows).' club resource progress records.');
    }

    private function seedAnnouncements(): void
    {
        $this->command?->info('Creating announcements...');

        $activeIds = $this->activeUserIds();

        $announcements = [
            ['title' => 'Welcome to SLAU CSIC 2026', 'type' => 'general', 'content' => '<p>Welcome to all new members joining SLAU CSIC this year!</p>'],
            ['title' => 'Upcoming Web Security Workshop', 'type' => 'event', 'content' => '<p>Join us for our upcoming workshop on web security.</p>'],
            ['title' => 'CTF Competition Results', 'type' => 'achievement', 'content' => '<p>Congratulations to all CTF participants!</p>'],
            ['title' => 'Weekly Meeting Reminder', 'type' => 'meeting', 'content' => '<p>Reminder: Weekly meeting holds this Tuesday.</p>'],
            ['title' => 'Membership Renewal Deadline', 'type' => 'urgent', 'content' => '<p>Please renew your membership before the deadline.</p>'],
            ['title' => 'New Members July 2026', 'type' => 'general', 'content' => '<p>Welcome to our newest members!</p>'],
            ['title' => 'Career Fair Announcement', 'type' => 'event', 'content' => '<p>Annual cybersecurity career fair coming soon.</p>'],
            ['title' => 'National Cyber Challenge Victory', 'type' => 'achievement', 'content' => '<p>Our team won 1st place at the National Cyber Challenge!</p>'],
            ['title' => 'Hackathon Registration Open', 'type' => 'event', 'content' => '<p>Registration for the 48-hour hackathon is now open.</p>'],
            ['title' => 'Lab Maintenance Schedule', 'type' => 'general', 'content' => '<p>Lab will be closed for maintenance this weekend.</p>'],
            ['title' => 'Guest Lecture: Cloud Security', 'type' => 'event', 'content' => '<p>Industry expert to speak on cloud security best practices.</p>'],
            ['title' => 'Exam Registration Open', 'type' => 'general', 'content' => '<p>Sign up for certification exam preparation sessions.</p>'],
            ['title' => 'Bug Bounty Program Launch', 'type' => 'achievement', 'content' => '<p>We are launching an internal bug bounty program!</p>'],
            ['title' => 'Q3 Planning Meeting', 'type' => 'meeting', 'content' => '<p>Board meeting to plan Q3 activities.</p>'],
            ['title' => 'Phishing Alert: Stay Vigilant', 'type' => 'urgent', 'content' => '<p>Be aware of phishing attempts targeting students.</p>'],
            ['title' => 'Workshop Feedback Survey', 'type' => 'general', 'content' => '<p>Help us improve by filling out the feedback form.</p>'],
            ['title' => 'Discord Server Update', 'type' => 'general', 'content' => '<p>Discord server has been reorganized with new channels.</p>'],
            ['title' => 'Mentorship Program Launch', 'type' => 'achievement', 'content' => '<p>New mentorship program pairing seniors with juniors.</p>'],
            ['title' => 'End of Semester Party', 'type' => 'event', 'content' => '<p>Celebrate the end of semester with us!</p>'],
            ['title' => 'Election Results Published', 'type' => 'general', 'content' => '<p>Results for the cabinet elections are now available.</p>'],
        ];

        foreach ($announcements as $i => $data) {
            \App\Models\Announcement::create([
                'title' => $data['title'],
                'slug' => Str::slug($data['title']),
                'content' => $data['content'],
                'type' => $data['type'],
                'audience' => 'all',
                'is_published' => true,
                'published_at' => $this->now->copy()->subDays(count($announcements) - $i)->setTime(9, 0),
                'created_by' => $this->adminId,
            ]);
        }

        $this->command?->info('Created '.count($announcements).' announcements.');
    }

    private function seedBadgeAwards(): void
    {
        $this->command?->info('Creating badge awards...');

        $badgeIds = Badge::pluck('id')->toArray();
        $activeIds = $this->activeUserIds();

        $batch = [];
        foreach ($activeIds as $uid) {
            $numBadges = min(mt_rand(1, 5), count($badgeIds));
            $awarded = [];
            for ($j = 0; $j < $numBadges; $j++) {
                $badgeId = $this->randomElement($badgeIds);
                if (in_array($badgeId, $awarded)) {
                    continue;
                }
                $awarded[] = $badgeId;
                $batch[] = [
                    'user_id' => $uid,
                    'badge_id' => $badgeId,
                    'earned_at' => $this->randomDate($this->now->copy()->subYear(), $this->now),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (count($batch) >= 500) {
                DB::table('user_badges')->insert($batch);
                $batch = [];
            }
        }

        if (! empty($batch)) {
            DB::table('user_badges')->insert($batch);
        }

        $count = DB::table('user_badges')->whereIn('user_id', $this->userIds)->count();
        $this->command?->info("Created {$count} badge awards.");
    }

    private function seedPointTransactions(): void
    {
        $this->command?->info('Creating point transactions...');

        $activeIds = $this->activeUserIds();

        $reasons = [
            'Event attendance bonus', 'CTF challenge solved', 'Workshop participation',
            'Meeting attendance', 'Referral bonus', 'Competition prize',
            'Badge award bonus', 'Community contribution', 'Writeup submission',
            'Mentorship activity',
        ];

        $batch = [];
        foreach ($activeIds as $uid) {
            $numTxns = mt_rand(0, 5);
            for ($j = 0; $j < $numTxns; $j++) {
                $batch[] = [
                    'user_id' => $uid,
                    'points' => mt_rand(10, 500),
                    'reason' => $this->randomElement($reasons),
                    'reference_type' => $this->randomElement(['event', 'ctf', 'badge', 'meeting', null]),
                    'reference_id' => mt_rand(1, 100),
                    'created_at' => $this->randomDate($this->now->copy()->subYear(), $this->now),
                    'updated_at' => now(),
                ];
            }

            if (count($batch) >= 500) {
                DB::table('point_transactions')->insert($batch);
                $batch = [];
            }
        }

        if (! empty($batch)) {
            DB::table('point_transactions')->insert($batch);
        }

        $count = DB::table('point_transactions')->whereIn('user_id', $this->userIds)->count();
        $this->command?->info("Created {$count} point transactions.");
    }
}
