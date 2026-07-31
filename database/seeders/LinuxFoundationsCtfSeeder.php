<?php

namespace Database\Seeders;

use App\Models\CtfCategory;
use App\Models\CtfChallenge;
use App\Models\CtfChallengeFile;
use App\Models\CtfCompetition;
use App\Models\CtfHint;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LinuxFoundationsCtfSeeder extends Seeder
{
    /**
     * SLAU CSIC Linux Foundations CTF
     *
     * 35 challenges designed to evaluate fundamental Linux skills.
     * Solo competition — no hints, 72-hour window.
     * Top 3 earn certificates (1st, 2nd, 3rd place).
     */
    public function run(): void
    {
        $competition = CtfCompetition::firstOrCreate(
            ['slug' => 'linux-foundations-ctf'],
            [
                'title' => 'SLAU CSIC Linux Foundations CTF',
                'description' => "A professional skills evaluation designed to assess fundamental Linux competencies. This solo CTF covers file system navigation, permissions, text processing, file analysis, encoding, scripting, and networking.\n\n🏆 Top 3 finishers earn achievement certificates.\n⏱ Duration: 72 hours from activation.\n📝 No hints available — pure skill assessment.\n🎯 Total possible: 10,875 points across 35 challenges.",
                'status' => 'published',
                'is_public' => true,
                'start_date' => now(),
                'end_date' => now()->addDays(3),
                'allow_teams' => false,
                'max_team_size' => 1,
                'max_score' => 10875,
            ]
        );

        $categories = $this->createCategories();

        $this->createChallenges($competition, $categories);
    }

    protected function createCategories(): array
    {
        $defs = [
            ['name' => 'Navigation & Basics', 'slug' => 'nav-basics', 'color' => '#10b981', 'icon' => '📁', 'sort_order' => 0],
            ['name' => 'File Operations', 'slug' => 'file-ops', 'color' => '#3b82f6', 'icon' => '📄', 'sort_order' => 1],
            ['name' => 'Text Processing', 'slug' => 'text-processing', 'color' => '#8b5cf6', 'icon' => '📝', 'sort_order' => 2],
            ['name' => 'Permissions & Ownership', 'slug' => 'permissions', 'color' => '#f59e0b', 'icon' => '🔒', 'sort_order' => 3],
            ['name' => 'Analysis & Forensics', 'slug' => 'analysis', 'color' => '#ef4444', 'icon' => '🔍', 'sort_order' => 4],
            ['name' => 'Scripting & Networking', 'slug' => 'scripting-net', 'color' => '#06b6d4', 'icon' => '⚙️', 'sort_order' => 5],
        ];

        $cats = [];
        foreach ($defs as $def) {
            $cats[$def['slug']] = CtfCategory::firstOrCreate(
                ['slug' => $def['slug']],
                $def
            );
        }

        return $cats;
    }

    protected function createChallenges(CtfCompetition $competition, array $categories): void
    {
        $challenges = [
            // ── TIER 1: BEGINNER (300 pts) ──────────────────────────────────
            [
                'title' => 'First Steps',
                'description' => "The Linux filesystem is a tree structure where everything starts from root (/). Every file, every directory, every device — all branch from that single root. Navigation commands like pwd (where am I?), ls (what's here?), and cd (go somewhere) are the first tools you'll master.\n\nKevin just set up his first Ubuntu server and found a mysterious file on the desktop. He needs to figure out where he is in the filesystem, navigate to the right directory, and read the file's contents. Every Linux journey starts with these three commands.\n\nDownload the attached file and read its contents to find the flag.",
                'flag' => 'SLAU_CSIC{welcome_to_linux}',
                'points' => 50,
                'difficulty' => 'easy',
                'category' => 'nav-basics',
                'tags' => ['navigation', 'pwd', 'ls', 'cd', 'cat'],
                'hint' => 'Use the `cat` command to read file contents.',
                'sort_order' => 1,
                'files' => ['flag.txt'],
            ],
            [
                'title' => 'Hidden in Plain Sight',
                'description' => "In Linux, files starting with a dot (.) are hidden from normal ls output. This isn't encryption — it's just a convention. System administrators use hidden files for configuration: .bashrc, .ssh/config, .env. Knowing how to find them is essential.\n\nMirembe was auditing a compromised server and noticed the attacker had hidden a backdoor script as a dotfile in the web root. Standard ls showed nothing suspicious — but the file was definitely there. She needed to look deeper.\n\nDownload the attached archive (hide_and_seek.tar.gz). An archive is a single file that bundles multiple files together — like a zip file. The .tar.gz extension means it was first packed with tar (tape archive), then compressed with gzip. To extract it, use: tar -xzf hide_and_seek.tar.gz. The -x means extract, -z means decompress with gzip, and -f means file. After extracting, you'll see several files — but one of them is hidden. Use ls -a to find it.",
                'flag' => 'SLAU_CSIC{hidden_files_discovered}',
                'points' => 75,
                'difficulty' => 'easy',
                'category' => 'nav-basics',
                'tags' => ['hidden', 'dotfiles', 'ls', '-a'],
                'hint' => 'Hidden files start with a dot. Try `ls -la` to see everything.',
                'sort_order' => 2,
                'files' => ['hide_and_seek.tar.gz'],
            ],
            [
                'title' => 'Read the Signs',
                'description' => "The file command inspects a file's actual contents to determine its type — regardless of what the filename says. A file called image.png might actually be a text file, and a file called data.txt might be a compiled binary. Never trust extensions in Linux.\n\nShafic received a file called mystery.jpg from an unknown source during a CTF competition. His instincts told him it wasn't really a JPEG. The file command confirmed his suspicion — and revealed something unexpected inside.\n\nDownload the attached file called mystery.jpg. But is it really a JPEG?",
                'flag' => 'SLAU_CSIC{file_type_detection}',
                'points' => 100,
                'difficulty' => 'easy',
                'category' => 'file-ops',
                'tags' => ['file', 'type', 'strings', 'detection'],
                'hint' => 'The file extension might be lying. Use the `file` command to check what it really is.',
                'sort_order' => 3,
                'files' => ['mystery.jpg'],
            ],
            [
                'title' => 'Permission Denied',
                'description' => "Linux permissions control who can read (r), write (w), and execute (x) files. Every file has an owner, a group, and others — represented as rwxrwxrwx. The chmod command changes these permissions, and understanding them is fundamental to system security.\n\nArafat found a file called secret.txt on the server that returned 'Permission denied' when he tried to read it. He checked the permissions with ls -la and realized the file had restrictive settings. He needed to adjust them before he could access the contents.\n\nDownload the attached file secret.txt. It has restrictive permissions — you need to change them to read it.",
                'flag' => 'SLAU_CSIC{permissions_mastered}',
                'points' => 100,
                'difficulty' => 'easy',
                'category' => 'permissions',
                'tags' => ['permissions', 'chmod', 'rwx', 'access'],
                'hint' => 'Check the file permissions with `ls -la`, then use `chmod` to fix them.',
                'sort_order' => 4,
                'files' => ['secret.txt'],
            ],

            // ── TIER 2: EASY (750 pts) ──────────────────────────────────────
            [
                'title' => 'Word Hunter',
                'description' => "grep is one of the most powerful tools in Linux. It searches file contents using patterns — think of it as Ctrl+F for the command line, but way more powerful. Security analysts use grep daily to search through thousands of log lines for indicators of compromise.\n\nJohnMary is a SOC analyst at a hosting company. A client reported suspicious activity on their web server. The access log has 10,000 lines of HTTP requests, and somewhere in there is evidence of the attacker's activity. He needs to find it — fast.\n\nDownload the attached access.log file and search for the pattern SLAU_CSIC to find the flag.",
                'flag' => 'SLAU_CSIC{grep_is_your_friend}',
                'points' => 125,
                'difficulty' => 'easy',
                'category' => 'text-processing',
                'tags' => ['grep', 'search', 'pattern', 'logs'],
                'hint' => 'Use `grep` with a pattern to search through thousands of lines instantly.',
                'sort_order' => 5,
                'files' => ['access.log'],
            ],
            [
                'title' => 'Line by Line',
                'description' => "head shows the first N lines of a file, tail shows the last N, and sed can extract any specific line. These three commands give you surgical precision when working with large text files — no need to open the whole file.\n\nShakira is a junior analyst reviewing a massive server configuration file with 5,000 lines. Her supervisor told her the critical setting is on line 4,217. She doesn't want to scroll through thousands of lines — she needs to extract that exact line.\n\nDownload the attached data.txt file. The flag is hidden on line number 4,217.",
                'flag' => 'SLAU_CSIC{line_4217_extracted}',
                'points' => 150,
                'difficulty' => 'easy',
                'category' => 'text-processing',
                'tags' => ['head', 'tail', 'sed', 'line', 'extraction'],
                'hint' => 'You can extract a specific line using `sed -n \'4217p\'` or `head`/`tail` piping.',
                'sort_order' => 6,
                'files' => ['data.txt'],
            ],
            [
                'title' => 'File Fingerprint',
                'description' => "Checksums like MD5 and SHA-256 create a unique digital fingerprint of a file. If even one byte changes, the checksum changes completely. System administrators use checksums to verify file integrity after transfers or updates.\n\nPatience is a sysadmin who downloaded a critical security patch from three different mirrors because the official one was slow. She needs to verify which mirror gave her the exact copy — no tampering, no corruption. One byte difference could mean malware injection.\n\nDownload the three attached files: original.txt, copy_a.txt, and copy_b.txt. Only one is an exact copy of the original.",
                'flag' => 'SLAU_CSIC{checksum_verified}',
                'points' => 150,
                'difficulty' => 'easy',
                'category' => 'analysis',
                'tags' => ['md5', 'sha256', 'checksum', 'integrity'],
                'hint' => 'Compare checksums with `md5sum` or `sha256sum` to find the identical copy.',
                'sort_order' => 7,
                'files' => ['original.txt', 'copy_a.txt', 'copy_b.txt'],
            ],
            [
                'title' => 'String Theory',
                'description' => "The strings command extracts readable text from any file — even binaries, images, or compiled programs. It scans for sequences of printable characters and outputs them. This is essential for forensic analysis when you can't open a file normally.\n\nOsio is a digital forensics analyst investigating a suspicious binary found on a suspect's machine. He can't run the program — it might be malware. But he needs to find any readable text hidden inside. The strings command is his safe way to extract information without executing the binary.\n\nDownload the attached mystery.bin file. Somewhere inside the binary data, there's a readable flag.",
                'flag' => 'SLAU_CSIC{strings_reveals_all}',
                'points' => 175,
                'difficulty' => 'easy',
                'category' => 'analysis',
                'tags' => ['strings', 'binary', 'forensics', 'extraction'],
                'hint' => 'Use `strings` to extract readable text from the binary.',
                'sort_order' => 8,
                'files' => ['mystery.bin'],
            ],

            // ── TIER 3: MEDIUM (1,200 pts) ──────────────────────────────────
            [
                'title' => 'Pipe Dream',
                'description' => "Pipes (|) are Linux's superpower. They connect commands together — the output of one becomes the input of the next. This lets you build complex data pipelines from simple building blocks. It's how real sysadmins process logs, filter data, and automate workflows.\n\nMirembe is investigating a data breach. She has a dump file with thousands of lines of random data, and she knows the flag is somewhere inside. She needs to chain commands together to filter, sort, and extract the needle from the haystack.\n\nDownload the attached raw_data.txt file. The flag is on a line that contains the word \"FLAG\".",
                'flag' => 'SLAU_CSIC{pipes_unleashed}',
                'points' => 200,
                'difficulty' => 'medium',
                'category' => 'text-processing',
                'tags' => ['pipe', 'grep', 'sort', 'chain'],
                'hint' => 'Chain commands with `|` to build a data pipeline that filters and extracts.',
                'sort_order' => 9,
                'files' => ['raw_data.txt'],
            ],
            [
                'title' => 'Sort & Destroy',
                'description' => "sort, uniq, and awk are the trifecta of text processing in Linux. sort orders lines, uniq removes duplicates and counts occurrences, and awk extracts columns. Together they can analyze any structured text file.\n\nKevin is auditing user activity logs for his company. He has a CSV with thousands of records and needs to find the user with the highest score. Scrolling through manually would take hours — but with sort and awk, it's one command.\n\nDownload the attached users.csv file. The flag is on the line with the highest score (column 3).",
                'flag' => 'SLAU_CSIC{sorted_and_found}',
                'points' => 200,
                'difficulty' => 'medium',
                'category' => 'text-processing',
                'tags' => ['sort', 'awk', 'csv', 'data'],
                'hint' => 'Use `sort -t, -k3 -rn` to sort CSV by a numeric column in reverse order.',
                'sort_order' => 10,
                'files' => ['users.csv'],
            ],
            [
                'title' => 'Archive Explorer',
                'description' => "Compressed archives are everywhere in Linux. tar bundles files together, gzip compresses them, and together they form .tar.gz files that are the standard for distributing software and backups. Every Linux administrator must know how to extract them.\n\nArafat found a backup archive on a compromised server. Inside it are nested directories — and somewhere deep in the tree is a file the attacker tried to hide. He needs to extract the archive and navigate through the directory structure to find it.\n\nDownload the attached challenge.tar.gz archive.",
                'flag' => 'SLAU_CSIC{archive_navigator}',
                'points' => 225,
                'difficulty' => 'medium',
                'category' => 'file-ops',
                'tags' => ['tar', 'gzip', 'archive', 'extraction'],
                'hint' => 'Extract with `tar -xzf` and navigate the directory tree inside.',
                'sort_order' => 11,
                'files' => ['challenge.tar.gz'],
            ],
            [
                'title' => 'Log Detective',
                'description' => "Log files record everything that happens on a system — login attempts, errors, web requests, cron jobs. They're the first place a sysadmin looks when something goes wrong, and the first place an attacker tries to cover their tracks.\n\nShafic is the incident response lead at a hosting company. A client's server was compromised, and he has three log files: auth.log, access.log, and error.log. Somewhere in these logs is the attacker's fingerprint — but it's buried among thousands of normal entries. He needs to find the anomaly.\n\nDownload the three attached log files. One of them contains a suspicious entry with the flag.",
                'flag' => 'SLAU_CSIC{log_analyst}',
                'points' => 275,
                'difficulty' => 'medium',
                'category' => 'analysis',
                'tags' => ['logs', 'grep', 'analysis', 'forensics'],
                'hint' => 'Use `grep -i` with multiple patterns to search across all log files at once.',
                'sort_order' => 12,
                'files' => ['auth.log', 'access.log', 'error.log'],
            ],
            [
                'title' => 'Hex Peek',
                'description' => "Hexadecimal (base-16) is how computers represent data at the lowest level. Every byte becomes two hex characters (0-9, a-f). Reading hex dumps is fundamental to binary analysis, reverse engineering, and debugging. Tools like xxd let you view and manipulate raw hex.\n\nJohnMary is reverse-engineering a corrupted firmware file. The file is too damaged to open normally, but a hex dump reveals patterns. He needs to decode the hex data to extract the embedded flag.\n\nDownload the attached hexdump.txt and encoded.bin files. The hexdump contains the encoded flag.",
                'flag' => 'SLAU_CSIC{hex_reader_pro}',
                'points' => 300,
                'difficulty' => 'medium',
                'category' => 'analysis',
                'tags' => ['hex', 'xxd', 'hexdump', 'binary'],
                'hint' => 'Use `xxd` to view the hex dump and `xxd -r -p` to reconstruct from hex.',
                'sort_order' => 13,
                'files' => ['hexdump.txt', 'encoded.bin'],
            ],

            // ── TIER 4: ADVANCED (1,600 pts) ────────────────────────────────
            [
                'title' => 'Script Reader',
                'description' => "Bash scripts automate repetitive tasks in Linux. Reading and understanding scripts is just as important as writing them — you'll often inherit scripts from colleagues or need to audit third-party code for security issues.\n\nOsio just joined a DevOps team and found an old automation script called process.sh in the deployment pipeline. Nobody remembers what it does, but it runs every night. Before he can approve it for production, he needs to understand its logic and determine what output it produces.\n\nDownload the attached process.sh script. Read it, understand its logic, and determine what output it would produce.",
                'flag' => 'SLAU_CSIC{script_master}',
                'points' => 350,
                'difficulty' => 'hard',
                'category' => 'scripting-net',
                'tags' => ['bash', 'script', 'logic', 'analysis'],
                'hint' => 'Read the script with `cat`, then run it with `bash` to see the output.',
                'sort_order' => 14,
                'files' => ['process.sh', 'data_input.txt'],
            ],
            [
                'title' => 'Process Inspector',
                'description' => "Every running program in Linux is a process with a unique PID. The /proc filesystem exposes detailed information about each process — its command line, environment variables, open files, and memory. Security analysts use process inspection to find malware and unauthorized services.\n\nShakira's server is acting strange — CPU usage is spiking every few minutes, but nothing obvious shows up in the standard monitoring tools. She suspects a hidden process is running in the background. She needs to dig into /proc to find it.\n\nThere's a hidden process running on this system that contains the flag. You need to find it.",
                'flag' => 'SLAU_CSIC{ps_aux_grep_mastery}',
                'points' => 350,
                'difficulty' => 'hard',
                'category' => 'scripting-net',
                'tags' => ['ps', 'proc', 'processes', 'system'],
                'hint' => 'Browse `/proc/[PID]/` directories — check `cmdline` and `environ` files.',
                'sort_order' => 15,
            ],
            [
                'title' => 'Network Navigator',
                'description' => "curl and wget are the Linux tools for fetching data from the web. curl is more versatile — it supports headers, authentication, and various protocols. These tools are essential for testing APIs, downloading files, and automating web interactions.\n\nPatience is setting up a new microservice and needs to verify it's responding correctly. She has a local API server running on port 8080 that she needs to test. She'll use curl to send requests and inspect the responses.\n\nThere's a local service running on port 8080 that serves the flag. Use curl or wget to fetch it.",
                'flag' => 'SLAU_CSIC{curl_and_http_decoded}',
                'points' => 400,
                'difficulty' => 'hard',
                'category' => 'scripting-net',
                'tags' => ['curl', 'wget', 'http', 'network'],
                'hint' => 'Use `curl` or `wget` to fetch data from the local service.',
                'sort_order' => 16,
            ],
            [
                'title' => 'Compiled Secrets',
                'description' => "When source code is compiled into a binary, the human-readable text becomes machine code. But not everything disappears — strings, constants, and data sections often retain readable text. Tools like strings and objdump let you peek inside compiled programs without running them.\n\nKevin found a suspicious binary on a production server that nobody installed. It's not in any package manager, and the team swears they didn't put it there. He needs to analyze it safely — without executing it — to find out what it contains.\n\nDownload the attached secret_program file. It's a compiled binary containing the flag embedded in its data section.",
                'flag' => 'SLAU_CSIC{strings_and_objdump}',
                'points' => 500,
                'difficulty' => 'hard',
                'category' => 'analysis',
                'tags' => ['binary', 'elf', 'strings', 'objdump'],
                'hint' => 'Use `strings` or `objdump -s -j .data` to read data sections from the binary.',
                'sort_order' => 17,
                'files' => ['secret_program'],
            ],

            // ── TIER 5: EXPERT (1,000 pts) ──────────────────────────────────
            [
                'title' => 'Multi-Stage Maze',
                'description' => "Real-world challenges rarely have a single step. This multi-stage challenge requires you to combine several Linux skills in sequence — each stage decodes or reveals information that leads to the next. It's like a puzzle where every answer unlocks a new question.\n\nMirembe is investigating a complex data exfiltration case. The attacker left behind a trail of encoded messages, each one containing instructions for the next step. She needs to decode them one by one, following the chain until she reaches the final flag.\n\nDownload the attached files. Stage 1: decode the contents of stage1.txt using base64. The decoded text contains instructions for the next stage.",
                'flag' => 'SLAU_CSIC{maze_completed}',
                'points' => 500,
                'difficulty' => 'insane',
                'category' => 'analysis',
                'tags' => ['multi-stage', 'base64', 'archive', 'chained'],
                'hint' => 'Decode stage 1 with `base64 -d` and follow the instructions step by step.',
                'sort_order' => 18,
                'files' => ['stage1.txt'],
            ],
            [
                'title' => 'The Gauntlet',
                'description' => "This is the ultimate test of everything you've learned in the Foundations CTF. It combines file analysis, binary inspection, text extraction, and creative problem-solving into one final challenge. There are no hints — just two files and your knowledge.\n\nArafat is going up against the final round of a national CTF competition. The organizers told him this challenge has stumped every participant so far. It requires combining multiple skills — file type detection, binary analysis, hex reading, and pattern recognition. This is what separates beginners from professionals.\n\nDownload the attached files. You have a binary (challenge_data.bin) and a text file (hidden.txt). Analyze both using every tool in your arsenal.",
                'flag' => 'SLAU_CSIC{gauntlet_conquered}',
                'points' => 500,
                'difficulty' => 'insane',
                'category' => 'analysis',
                'tags' => ['comprehensive', 'multi-skill', 'final'],
                'hint' => 'Try `file`, `strings`, `xxd`, and `cat` on every file. Combine what you find.',
                'sort_order' => 19,
                'files' => ['challenge_data.bin', 'hidden.txt'],
            ],

            // ── TIER 6: SYSTEM ADMINISTRATION ──────────────────────────────
            [
                'title' => 'The Great Search',
                'description' => "The find command is the ultimate file search tool in Linux. It can locate files by name, type, size, modification time, permissions, owner — almost any attribute you can think of. System administrators use it daily to find configuration files, locate large files, or track down suspicious content.\n\nShafic inherited a server with thousands of directories and no documentation. Somewhere in the file tree is a hidden flag file. He could browse directories manually for hours, or he could use find to search the entire tree in seconds.\n\nDownload the attached archive and extract it. Somewhere inside is a flag file. Use find to locate it.",
                'flag' => 'SLAU_CSIC{find_command_power}',
                'points' => 250,
                'difficulty' => 'medium',
                'category' => 'file-ops',
                'tags' => ['find', 'search', 'filesystem', 'recursive'],
                'hint' => 'Use `find` with `-name` or `-type f` to recursively locate files.',
                'sort_order' => 20,
                'files' => ['find_challenge.tar.gz', 'hint.sh'],
            ],
            [
                'title' => 'System Recon',
                'description' => "Before you can fix a system, you need to know what you're working with. uname, hostnamectl, and /etc/os-release reveal the operating system, kernel version, architecture, and more. This information is the foundation of system administration and security auditing.\n\nJohnMary is onboarding a new client's infrastructure. The first step is a system reconnaissance report — what kernel is running, how much RAM is installed, how long has the server been up. He has a report file and needs to extract the key details.\n\nDownload the attached system_report.txt. Parse it to answer: What kernel version is running? How much memory is installed? What is the uptime?",
                'flag' => 'SLAU_CSIC{uname_a_cat_os_release}',
                'points' => 250,
                'difficulty' => 'medium',
                'category' => 'analysis',
                'tags' => ['uname', 'system-info', 'reconnaissance'],
                'hint' => 'Look for kernel version, memory, and uptime in the report file.',
                'sort_order' => 21,
                'files' => ['system_report.txt'],
            ],
            [
                'title' => 'Environment Explorer',
                'description' => "Environment variables are key-value pairs that configure the shell and running programs. They store database credentials, API keys, language settings, and paths. Developers and sysadmins use them constantly — and security researchers know they're a common source of information leaks.\n\nMirembe is pentesting a web application and noticed the server exposes environment variables through a debug endpoint. She dumps them and starts looking for anything interesting — database passwords, internal API keys, hidden flags.\n\nDownload the attached env_dump.txt. Search through the variables to find the hidden flag.",
                'flag' => 'SLAU_CSIC{env_vars_leak_secrets}',
                'points' => 250,
                'difficulty' => 'medium',
                'category' => 'analysis',
                'tags' => ['env', 'environment', 'variables', 'secrets'],
                'hint' => 'Search for keywords like `flag`, `secret`, or `key` in the env dump.',
                'sort_order' => 22,
                'files' => ['env_dump.txt'],
            ],
            [
                'title' => 'Disk Detective',
                'description' => "Disk space management is a critical sysadmin skill. du shows how much space directories and files consume, and find can locate files by size. Together they help you track down space hogs, find logs that grew out of control, or locate hidden large files.\n\nKevin's server is running out of disk space. He ran df and confirmed the disk is almost full, but he can't figure out what's eating the space. He needs to find a hidden file consuming over 200MB somewhere in the filesystem.\n\nDownload the attached disk_report.txt. The report reveals a hidden large file. Find it.",
                'flag' => 'SLAU_CSIC{du_find_large_files}',
                'points' => 300,
                'difficulty' => 'medium',
                'category' => 'analysis',
                'tags' => ['du', 'find', 'disk-usage', 'large-files'],
                'hint' => 'Use `du -sh` and `find -size +200M` to locate oversized files.',
                'sort_order' => 23,
                'files' => ['disk_report.txt'],
            ],
            [
                'title' => 'Link Master',
                'description' => "Linux has two types of links: symbolic links (symlinks) and hard links. A symlink is a separate file that points to a path — if you delete the original, the symlink breaks. A hard link is an additional directory entry pointing to the same inode — even if you delete the original, the hard link still works because both names reference the same data on disk.\n\nArafat is auditing a legacy backup tree. The original secret file is buried seven directories deep. At the top level, he found four files that look identical: link_alpha.txt, link_beta.txt, link_gamma.txt, and link_delta.txt. Some are symlinks pointing to decoy files, and one is a hard link to the real secret. With `ls -la`, symlinks show an 'l' prefix and an arrow (->) pointing to their target. A hard link looks like a normal file — but `stat` reveals it shares the same inode as the original.\n\nDownload the attached archive and extract it. Use `ls -la` to spot the symlinks, then use `stat` on each file to find which one shares its inode with the buried secret.",
                'flag' => 'SLAU_CSIC{symlinks_and_hard_links}',
                'points' => 300,
                'difficulty' => 'medium',
                'category' => 'file-ops',
                'tags' => ['symlink', 'hard-link', 'inode', 'stat'],
                'hint' => 'Use `ls -la` to see which are symlinks, then `stat` on each file to compare inode numbers.',
                'sort_order' => 24,
                'files' => ['link_challenge.tar.gz'],
            ],
            [
                'title' => 'Temp Files',
                'description' => "The /tmp directory is a dumping ground for temporary files from every process on the system. Finding specific files in this chaos requires time-based filtering — using find's -mmin and -newer options to narrow down by modification time.\n\nPatience is doing a forensic analysis of a compromised server. The attacker dumped files in /tmp, but there are dozens of temporary files. She knows the attack happened more than 45 minutes ago, so she needs to find files older than that to isolate the attacker's artifacts.\n\nDownload the attached temp_files.txt. The secret archive was created before 07:30. Use find's time-based options to identify old files.",
                'flag' => 'SLAU_CSIC{find_time_based_filtering}',
                'points' => 350,
                'difficulty' => 'hard',
                'category' => 'file-ops',
                'tags' => ['find', 'tmp', 'time', 'mmin'],
                'hint' => 'Use `find /tmp -mmin +45` to filter files by modification time.',
                'sort_order' => 25,
                'files' => ['temp_files.txt'],
            ],

            // ── TIER 7: ENCODING & DECODING ─────────────────────────────────
            [
                'title' => 'Caesar Salad',
                'description' => "ROT13 is a Caesar cipher that shifts each letter 13 positions in the alphabet. It's the simplest encoding scheme — Julius Caesar used a version of it over 2,000 years ago. It's symmetric: applying ROT13 twice gives you back the original text. While trivial to break, it teaches the fundamentals of cryptography.\n\nShakira intercepted an encrypted message from a rival CTF team. She immediately recognized it as ROT13 — the letter patterns matched. She needs to decode it to find out what they're planning.\n\nDownload the attached encoded_message.txt. The flag has been ROT13-encoded.",
                'flag' => 'SLAU_CSIC{caesar_cipher_cracked}',
                'points' => 350,
                'difficulty' => 'hard',
                'category' => 'text-processing',
                'tags' => ['rot13', 'caesar', 'tr', 'decode'],
                'hint' => 'ROT13 is a 13-position letter shift. Use `tr \'A-Za-z\' \'N-ZA-Mn-za-m\'` to decode.',
                'sort_order' => 26,
                'files' => ['encoded_message.txt'],
            ],
            [
                'title' => 'Log Parser',
                'description' => "awk is the Swiss Army knife of text processing in Linux. It splits lines into fields, performs calculations, and outputs formatted results. When combined with sort and uniq, it becomes a powerful analysis tool for structured data like logs, CSVs, and reports.\n\nOsio is analyzing web server access logs to identify the most frequently accessed endpoint. His security team suspects an attacker was repeatedly hitting a specific API. He needs to count occurrences of each endpoint and find the one with the most hits.\n\nDownload the attached access_logs.txt. Find the HTTP endpoint accessed most frequently using awk, sort, and uniq.",
                'flag' => 'SLAU_CSIC{sort_uniq_count_mastery}',
                'points' => 400,
                'difficulty' => 'hard',
                'category' => 'text-processing',
                'tags' => ['awk', 'sort', 'uniq', 'counting'],
                'hint' => 'Pipe `awk` output through `sort | uniq -c | sort -rn` to count occurrences.',
                'sort_order' => 27,
                'files' => ['access_logs.txt'],
            ],
            [
                'title' => 'Regex Raider',
                'description' => "Regular expressions (regex) are patterns that match text. grep -E enables extended regex, which supports quantifiers like + and {}, character classes like [0-9], and anchors like ^ and $. Regex is used everywhere — from log analysis to web scraping to security tools.\n\nKevin is analyzing network traffic logs from a compromised server. He needs to find all ERROR entries originating from the 172.16.x.x network, destined for port 3306 (MySQL), with more than 4000 bytes transferred. Writing the right regex pattern is the key to filtering exactly what he needs.\n\nDownload the attached network_log.txt. Use grep -E with extended regex to find matching entries.",
                'flag' => 'SLAU_CSIC{grep_extended_regex_prowess}',
                'points' => 400,
                'difficulty' => 'hard',
                'category' => 'text-processing',
                'tags' => ['grep', 'regex', 'extended', 'filtering'],
                'hint' => 'Build a single `grep -E` pattern that matches all criteria at once.',
                'sort_order' => 28,
                'files' => ['network_log.txt'],
            ],
            [
                'title' => 'Permission Audit',
                'description' => "SUID (Set User ID) binaries run with the privileges of the file owner — usually root. They're essential for tasks like changing passwords (passwd uses SUID). But a misconfigured SUID binary with world-writable permissions is a critical security vulnerability that can lead to privilege escalation.\n\nShafic is performing a security audit on a production server. He's scanning for SUID binaries — programs that run with elevated privileges. Most are standard system tools, but one has abnormally permissive settings that could allow any user to gain root access.\n\nDownload the attached suid_report.txt. One binary has abnormal permissions (world-writable) — that's the target.",
                'flag' => 'SLAU_CSIC{find_suid_abnormal_permissions}',
                'points' => 400,
                'difficulty' => 'hard',
                'category' => 'permissions',
                'tags' => ['suid', 'permissions', 'audit', 'security'],
                'hint' => 'Look for the `rwsrwxrwx` permission pattern — that\'s the abnormal SUID binary.',
                'sort_order' => 29,
                'files' => ['suid_report.txt'],
            ],
            [
                'title' => 'User Enumeration',
                'description' => "/etc/passwd is a plain-text database of every user on the system. Each line has 7 colon-separated fields: username, password placeholder (x), UID, GID, comment, home directory, and login shell. System administrators parse it to audit users, find shell access, and identify unusual accounts.\n\nArafat is doing a security audit and needs to understand the user landscape on a server. He has a dump of /etc/passwd and needs to answer: How many users can actually log in? Which UID is shared by the most accounts? And who owns UID 1000?\n\nDownload the attached passwd_audit.txt. Parse it to answer these questions.",
                'flag' => 'SLAU_CSIC{awk_passwd_field_parsing}',
                'points' => 450,
                'difficulty' => 'hard',
                'category' => 'analysis',
                'tags' => ['awk', 'passwd', 'parsing', 'users'],
                'hint' => 'Parse with `awk -F:` to split colon-separated fields and filter by shell.',
                'sort_order' => 30,
                'files' => ['passwd_audit.txt'],
            ],
            [
                'title' => 'Cron Inspector',
                'description' => "Cron is Linux's built-in task scheduler. It runs commands automatically at specified times — every minute, every hour, daily, weekly, or on custom schedules. The crontab format has 5 time fields (minute, hour, day-of-month, month, day-of-week) followed by the command. Understanding cron is essential for automation and security.\n\nMirembe is investigating a compromised server and found a suspicious entry in the crontab. The attacker set up a flag rotation script that runs at a specific time with a hidden argument. She needs to decode the cron schedule and find the secret argument.\n\nDownload the attached crontab.txt. Find the rotate_flags.sh entry and extract the secret argument.",
                'flag' => 'SLAU_CSIC{cron_scheduling_time_fields}',
                'points' => 450,
                'difficulty' => 'hard',
                'category' => 'scripting-net',
                'tags' => ['cron', 'crontab', 'scheduling', 'parsing'],
                'hint' => 'Read the crontab entry carefully — the secret is in the command arguments.',
                'sort_order' => 31,
                'files' => ['crontab.txt'],
            ],
            [
                'title' => 'SQLite Sleuth',
                'description' => "SQLite is the world's most widely deployed database engine. Unlike MySQL or PostgreSQL, it stores everything in a single file. It's used in mobile apps, browsers, embedded systems, and even some web applications. The sqlite3 command-line tool lets you query SQLite databases directly from the terminal.\n\nJohnMary found a SQLite database file on a suspect's laptop during a forensic investigation. It contains a users table and a flags table with hidden data. He needs to explore the database structure and query the flags table to extract the secret.\n\nDownload the attached users.db. Use sqlite3 to explore the tables and query for the flag.",
                'flag' => 'SLAU_CSIC{sqlite_select_where}',
                'points' => 500,
                'difficulty' => 'hard',
                'category' => 'analysis',
                'tags' => ['sqlite', 'database', 'sql', 'query'],
                'hint' => 'Use `sqlite3` with `.tables`, `.schema`, and SQL `SELECT` queries.',
                'sort_order' => 32,
                'files' => ['users.db'],
            ],
            [
                'title' => 'Base64 Breaker',
                'description' => "Base64 encodes binary data as printable ASCII text. Every 3 bytes become 4 characters, always ending with = or == padding. You'll see it everywhere — in email attachments, JWT tokens, API responses, and data URLs. It's not encryption (anyone can decode it), but it's the standard way to safely transmit binary data as text.\n\nShakira is analyzing an intercepted network transmission. The payload appears to be Base64-encoded data. She needs to decode it to reveal the hidden message — and possibly a flag.\n\nDownload the attached encoded_data.txt. The flag is encoded in Base64 on the first line.",
                'flag' => 'SLAU_CSIC{base64_decode_mastered}',
                'points' => 500,
                'difficulty' => 'hard',
                'category' => 'text-processing',
                'tags' => ['base64', 'encoding', 'decode', 'ascii'],
                'hint' => 'Pipe the encoded text through `base64 -d` to decode it.',
                'sort_order' => 33,
                'files' => ['encoded_data.txt'],
            ],
            [
                'title' => 'Script Debugger',
                'description' => "Bash scripting is powerful but unforgiving. Unquoted variables cause word splitting, wrong comparison operators produce silent failures, and missing quotes around array elements skip items. Debugging bash scripts requires attention to detail and knowledge of these common pitfalls.\n\nPatience inherited a user management script from a former colleague. It's supposed to check if a user exists and print their info, but it crashes on certain inputs. She needs to read through the code and identify the 3 intentional bugs hiding in it.\n\nDownload the attached broken_script.sh and hint.txt. The script has 3 intentional bugs. Identify all of them.",
                'flag' => 'SLAU_CSIC{bash_debugging_skills}',
                'points' => 500,
                'difficulty' => 'hard',
                'category' => 'scripting-net',
                'tags' => ['bash', 'debugging', 'scripting', 'bugs'],
                'hint' => 'Look for unquoted variables, wrong comparison operators, and array expansion bugs.',
                'sort_order' => 34,
                'files' => ['broken_script.sh', 'hint.txt'],
            ],
            [
                'title' => 'Netstat Ninja',
                'description' => "netstat and ss are network diagnostic tools that reveal active connections, listening ports, and associated processes. Security analysts use them to identify unauthorized services, trace connections to suspicious IPs, and map the network footprint of a running system.\n\nKevin is investigating a data exfiltration incident. He knows the attacker's malware communicates with an external server on port 8080. He needs to find which process is listening on that port and which external IP is connected to it — that's where the stolen data is going.\n\nDownload the attached netstat_output.txt. A CTF API server is listening on port 8080. Find which external IP is connected to it.",
                'flag' => 'SLAU_CSIC{netstat_ss_network_analysis}',
                'points' => 500,
                'difficulty' => 'hard',
                'category' => 'scripting-net',
                'tags' => ['netstat', 'ss', 'network', 'connections'],
                'hint' => 'Use `netstat -tnp` or `ss -tlnp` to find connections on the target port.',
                'sort_order' => 35,
                'files' => ['netstat_output.txt'],
            ],
        ];

        foreach ($challenges as $data) {
            $slug = Str::slug($data['title']);
            $category = $categories[$data['category']] ?? $categories['nav-basics'];

            $existing = CtfChallenge::where('ctf_competition_id', $competition->id)
                ->where('slug', $slug)
                ->exists();

            if ($existing) {
                $this->command->info("  Skipping: {$data['title']} (already exists)");

                continue;
            }

            $challenge = CtfChallenge::create([
                'ctf_competition_id' => $competition->id,
                'ctf_category_id' => $category->id,
                'title' => $data['title'],
                'slug' => $slug,
                'description' => $data['description'],
                'flag_hash' => hash('sha256', strtolower($data['flag'])),
                'points' => $data['points'],
                'difficulty' => $data['difficulty'],
                'is_active' => true,
                'max_attempts' => 0,
                'sort_order' => $data['sort_order'],
                'tags' => $data['tags'] ?? [],
                'flag_case_sensitive' => false,
            ]);

            // Create file records for challenges with downloadable files
            if (isset($data['files'])) {
                foreach ($data['files'] as $fileName) {
                    $storedPath = 'ctf/linux-foundations/'.str_pad($data['sort_order'], 2, '0', STR_PAD_LEFT).'/'.$fileName;
                    $fullPath = Storage::disk('local')->path($storedPath);

                    if (file_exists($fullPath)) {
                        CtfChallengeFile::create([
                            'ctf_challenge_id' => $challenge->id,
                            'original_name' => $fileName,
                            'stored_path' => $storedPath,
                            'mime_type' => mime_content_type($fullPath) ?: 'application/octet-stream',
                            'file_size' => filesize($fullPath),
                        ]);
                    }
                }
            }

            // Create hint (free, always visible like PicoCTF)
            if (isset($data['hint'])) {
                CtfHint::create([
                    'ctf_challenge_id' => $challenge->id,
                    'tier' => 0,
                    'content' => $data['hint'],
                    'cost' => 0,
                ]);
            }

            $this->command->info("  Created: {$data['title']} ({$data['points']} pts, {$data['difficulty']})");
        }

        $total = $competition->challenges()->count();
        $points = $competition->challenges()->sum('points');
        $this->command->info("\n✓ Competition: {$competition->title}");
        $this->command->info("  Challenges: {$total}");
        $this->command->info("  Total points: {$points}");
        $this->command->info("  URL: /ctf/{$competition->slug}");
    }
}
