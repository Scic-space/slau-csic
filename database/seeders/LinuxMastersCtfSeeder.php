<?php

namespace Database\Seeders;

use App\Models\CtfChallenge;
use App\Models\CtfChallengeFile;
use App\Models\CtfCompetition;
use App\Models\CtfHint;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class LinuxMastersCtfSeeder extends Seeder
{
    public function run(): void
    {
        $competition = CtfCompetition::firstOrCreate(
            ['slug' => 'linux-masters-ctf'],
            [
                'title' => 'SLAU CSIC Linux Masters CTF',
                'description' => "An advanced skills evaluation for experienced Linux practitioners. This solo CTF covers reverse engineering, digital forensics, and cryptography.\n\nTop 3 finishers earn achievement certificates.\nDuration: 72 hours from activation.\nNo hints available — pure skill assessment.\nTotal possible: 17,600 points across 30 challenges.",
                'status' => 'published',
                'is_public' => true,
                'start_date' => now(),
                'end_date' => now()->addDays(7),
                'allow_teams' => false,
                'max_team_size' => 1,
                'max_score' => 17600,
            ]
        );

        $categories = $this->createCategories();
        $this->createChallenges($competition, $categories);
    }

    protected function createCategories(): array
    {
        $defs = [
            ['id' => 14, 'name' => 'Reverse Engineering', 'slug' => 'reverse-engineering', 'color' => 'primary', 'icon' => 'heroicon-o-cpu-chip', 'sort_order' => 0],
            ['id' => 15, 'name' => 'Digital Forensics', 'slug' => 'digital-forensics', 'color' => 'success', 'icon' => 'heroicon-o-magnifying-glass', 'sort_order' => 1],
            ['id' => 16, 'name' => 'Cryptography', 'slug' => 'cryptography', 'color' => 'warning', 'icon' => 'heroicon-o-key', 'sort_order' => 2],
            ['id' => 17, 'name' => 'Steganography', 'slug' => 'steganography', 'color' => 'danger', 'icon' => 'heroicon-o-eye', 'sort_order' => 3],
            ['id' => 18, 'name' => 'System Exploitation', 'slug' => 'system-exploitation', 'color' => 'info', 'icon' => 'heroicon-o-shield-exclamation', 'sort_order' => 4],
        ];

        $cats = [];
        foreach ($defs as $def) {
            $cats[$def['slug']] = \App\Models\CtfCategory::firstOrCreate(
                ['id' => $def['id']],
                $def
            );
        }

        return $cats;
    }

    protected function createChallenges(CtfCompetition $competition, array $categories): void
    {
        $challenges = [
            // ── REVERSE ENGINEERING (Category 14) ──
            [
                'title' => 'Buffer Overflow',
                'slug' => 'buffer-overflow-v2',
                'description' => "When variables sit next to each other in memory, overwriting one can corrupt its neighbors — and that corruption can become your greatest weapon.\n\nOpiyo deployed a simple authentication server written in C. It reads a token into a fixed-size buffer and checks if you're authorized. The catch? The buffer and the authorization flag live side by side in memory. A carefully oversized token can bleed into the flag's memory and flip it from false to true.\n\nDownload vuln_server.c and study the struct layout. Find the right input that overflows the token buffer and overwrites the adjacent boolean. When authorization flips, flag.txt becomes accessible.",
                'flag' => 'SLAU_CSIC{b4by_0v3rfl0w_1s_d4ng3r0us}',
                'points' => 400,
                'difficulty' => 'hard',
                'category' => 'reverse-engineering',
                'tags' => ['buffer-overflow', 'c', 'struct-layout'],
                'hint' => 'Study the struct layout in the source — the auth_token buffer is adjacent to the boolean flag in memory.',
                'sort_order' => 1,
                'files' => ['vuln_server.c', 'vuln_server', 'flag.txt'],
            ],
            [
                'title' => 'Python Packer',
                'slug' => 'python-packer-v2',
                'description' => "Security through obscurity rarely works — but sometimes watching someone try it reveals the flag hiding underneath.\n\nShafic wrote a script that hides the flag behind five nested layers of obfuscation: XOR with a rolling key, base64 encoding, another XOR, more base64, and a final XOR scramble. Each layer wraps the previous one like Russian nesting dolls.\n\nDownload packed.py and deobfuscate layer by layer. Work backwards — identify the outermost encoding first, reverse it, and repeat until the flag emerges from the chaos.",
                'flag' => 'SLAU_CSIC{pyth0n_l4y3rs_g0_d33p}',
                'points' => 500,
                'difficulty' => 'hard',
                'category' => 'reverse-engineering',
                'tags' => ['python', 'obfuscation', 'deobfuscation'],
                'hint' => 'Work backwards from the outermost encoding layer. Identify, reverse, repeat.',
                'sort_order' => 2,
                'files' => ['packed.py'],
            ],
            [
                'title' => 'x86 Disassembly',
                'slug' => 'x86-disasm-v2',
                'description' => "Reading raw assembly is like deciphering a secret language — each instruction tells a piece of the story, and understanding the pattern reveals the answer.\n\nArafat intercepted a disassembly listing from a binary that validates a passphrase. There's no source code, only the raw x86 instructions. The verify_flag function XORs each character of the input against a hardcoded key and compares the result against a stored array.\n\nDownload disasm.txt and study the assembly. Identify the XOR constant and the comparison array. Reverse the XOR operation on each byte to recover the original flag from the encoded values.",
                'flag' => 'SLAU_CSIC{x86_disassembly_decoded}',
                'points' => 600,
                'difficulty' => 'hard',
                'category' => 'reverse-engineering',
                'tags' => ['x86', 'assembly', 'xor', 'reverse-engineering'],
                'hint' => 'The verify_flag function XORs each input byte against a constant. Find the XOR_KEY array values and reverse the operation.',
                'sort_order' => 3,
                'files' => ['disasm.txt'],
            ],
            [
                'title' => 'Custom VM',
                'slug' => 'custom-vm-v2',
                'description' => "When someone builds their own computer inside a program, you need to become the computer to understand what it's saying.\n\nJohnMary created a stack-based virtual machine with its own set of opcodes, registers, and memory. The bytecode in challenge.bin contains the encoded flag, but to read it you need to understand how the VM works and execute the instructions yourself.\n\nDownload vm_spec.txt for the complete VM specification — opcodes, register layout, and stack behavior. Then download challenge.bin and either implement the VM in Python or trace through the bytecode manually to decode the flag.",
                'flag' => 'SLAU_CSIC{v1rtu4l_m4ch1n3_r3v3rs3d}',
                'points' => 800,
                'difficulty' => 'expert',
                'category' => 'reverse-engineering',
                'tags' => ['virtual-machine', 'bytecode', 'interpreter'],
                'hint' => 'Implement the VM specification in Python, then execute the bytecode to print the flag.',
                'sort_order' => 4,
                'files' => ['vm_spec.txt', 'challenge.bin'],
            ],
            [
                'title' => 'Algorithm RE',
                'slug' => 'algo-re-v2',
                'description' => "The best encryption is useless if you can study both the lock and the key — and the lock happens to be reversible.\n\nShakira designed a custom encryption algorithm using S-box substitution, transposition, and modular arithmetic. She left both the encryption script and the encrypted flag, but only the encryption function. Your job is to understand the algorithm well enough to reverse it.\n\nDownload cipher.py and encrypted.txt. Study each transformation step — substitution, transposition, arithmetic — then write your own decryption function that undoes them in the correct reverse order.",
                'flag' => 'SLAU_CSIC{r3v3rs3_th3_c1ph3r_4lg0r1thm}',
                'points' => 700,
                'difficulty' => 'expert',
                'category' => 'reverse-engineering',
                'tags' => ['custom-cipher', 'sbox', 'transposition'],
                'hint' => 'Reverse each transformation step (substitution, transposition, arithmetic) in the correct opposite order.',
                'sort_order' => 5,
                'files' => ['cipher.py', 'encrypted.txt'],
            ],
            [
                'title' => 'Binary Protocol',
                'slug' => 'bin-proto-v2',
                'description' => "Every network protocol follows a grammar — magic bytes declare identity, headers define structure, and payloads carry secrets. When the grammar is undocumented, reverse engineering becomes your reading comprehension.\n\nKevin intercepted a binary capture of a custom network protocol. The protocol uses magic bytes for identification, a version field, different packet types for different operations, and type-0x03 packets carry the actual flag data.\n\nDownload capture.bin and analyze the binary structure. Identify the magic bytes, parse the header format, enumerate the packet types, and extract the flag from the correct packet type.",
                'flag' => 'SLAU_CSIC{n3tw0rk_pr0t0c0l_4n4lyz3d}',
                'points' => 600,
                'difficulty' => 'hard',
                'category' => 'reverse-engineering',
                'tags' => ['binary', 'protocol', 'network'],
                'hint' => 'Identify the magic bytes for protocol detection, then parse the header to find Type-0x03 packets.',
                'sort_order' => 6,
                'files' => ['capture.bin'],
            ],
            [
                'title' => 'Logic Circuit',
                'slug' => 'logic-circuit-v2',
                'description' => "A circuit that encrypts by scrambling bits can decrypt by scrambling them again — if it's its own inverse.\n\nPatience designed a boolean circuit described as a netlist. It takes input bits, passes them through layers of XOR and AND gates, and produces output bits. The remarkable property? The circuit is its own inverse — feed the ciphertext back through the same circuit and the plaintext emerges.\n\nDownload circuit.txt and study the netlist. Given a set of input/output character pairs, work through the gate logic to determine what input produces the flag output. The self-inverse property means you can verify your answer by running it through twice.",
                'flag' => 'SLAU_CSIC{boolean_circuit_reversed}',
                'points' => 500,
                'difficulty' => 'hard',
                'category' => 'reverse-engineering',
                'tags' => ['boolean-logic', 'circuit', 'self-inverse'],
                'hint' => 'The circuit is its own inverse — pass the ciphertext back through the same circuit to get plaintext.',
                'sort_order' => 7,
                'files' => ['circuit.txt'],
            ],
            [
                'title' => 'Execution Trace',
                'slug' => 'exec-trace-v2',
                'description' => "Nested validation functions create a maze of conditions — but tracing the path through each layer reveals the one input that satisfies them all.\n\nMirembe wrote a Python program with six nested validation functions, each checking a different property of the input: length, character ranges, checksums, parity, and more. Only one specific input string passes every check, and the flag values are hardcoded in the source.\n\nDownload maze.py and trace the execution path through each validation layer. Map out the conditions, work backwards from the innermost check, and identify the exact input that satisfies all six functions simultaneously.",
                'flag' => 'SLAU_CSIC{13x3cuti0n_13}',
                'points' => 700,
                'difficulty' => 'expert',
                'category' => 'reverse-engineering',
                'tags' => ['python', 'tracing', 'validation'],
                'hint' => 'Map the validation conditions in each nested function, then find the input satisfying all of them.',
                'sort_order' => 8,
                'files' => ['maze.py'],
            ],
            [
                'title' => 'Symbolic Execution',
                'slug' => 'symbolic-exec-v2',
                'description' => "When a program validates your input through range checks, arithmetic, XOR operations, and checksums, brute force becomes impossible — but constraint solving makes it trivial.\n\nShafic's program validates 24 characters through a gauntlet of constraints: each character must fall within a specific range, satisfy modular arithmetic conditions, XOR correctly with its neighbors, and match a sliding window checksum. Every constraint narrows the solution space until only one input remains valid.\n\nDownload constrained.py and use Z3, angr, or manual constraint solving. Define each constraint symbolically and let the solver find the unique input that satisfies all conditions simultaneously.",
                'flag' => 'SLAU_CSIC{c0nstr41nt_s0}',
                'points' => 500,
                'difficulty' => 'hard',
                'category' => 'reverse-engineering',
                'tags' => ['z3', 'symbolic-execution', 'constraints'],
                'hint' => 'Define each constraint symbolically with Z3 and let the solver find the unique valid input.',
                'sort_order' => 9,
                'files' => ['constrained.py'],
            ],
            [
                'title' => 'Anti-Analysis',
                'slug' => 'anti-analysis-v2',
                'description' => "The best way to hide something is to make sure no one can look at it — but static analysis doesn't need the program to run.\n\nArafat built a Python script protected by multiple anti-analysis techniques: debugger detection that crashes on attach, file integrity checks that alter output, eval(compile()) obfuscation that hides the real logic, and runtime XOR that decrypts the flag only during execution.\n\nDownload protected.py and analyze it without running it. Study the source statically — identify the anti-analysis mechanisms, understand what they protect, and extract the flag through careful code reading rather than execution.",
                'flag' => 'SLAU_CSIC{4nt1_4n4lys1s_byp4ss3d}',
                'points' => 600,
                'difficulty' => 'hard',
                'category' => 'reverse-engineering',
                'tags' => ['anti-analysis', 'obfuscation', 'xor'],
                'hint' => 'Read the source statically without running it — identify protections, then extract the flag from the code.',
                'sort_order' => 10,
                'files' => ['protected.py'],
            ],

            // ── DIGITAL FORENSICS (Category 15) ──
            [
                'title' => 'PCAP Extract',
                'slug' => 'pcap-extract-v2',
                'description' => "Every HTTP request and response leaves a digital trail — and Wireshark is your magnifying glass for reading it.\n\nOpiyo captured network traffic during a suspicious session between a web server and client. The pcap file contains HTTP requests, DNS queries, and various protocol artifacts. Somewhere in that traffic, a server responded with the flag.\n\nDownload traffic.pcap and open it in Wireshark. Filter for HTTP traffic, follow the TCP streams, and examine the server responses. The flag is hiding in one of the HTTP response bodies.",
                'flag' => 'SLAU_CSIC{wireshark_is_your_friend}',
                'points' => 600,
                'difficulty' => 'hard',
                'category' => 'digital-forensics',
                'tags' => ['pcap', 'wireshark', 'http'],
                'hint' => 'Open in Wireshark, filter for HTTP, and follow the TCP stream to find the response with the flag.',
                'sort_order' => 11,
                'files' => ['traffic.pcap'],
            ],
            [
                'title' => 'Memory Strings',
                'slug' => 'memory-strings-v2',
                'description' => "Memory dumps are archaeological sites — buried beneath layers of system artifacts, the flag waits to be unearthed by anyone who knows how to dig.\n\nKevin analyzed a compromised system and captured a 512KB memory dump. The dump contains realistic system artifacts: process data, registry keys, temporary files, and scattered flag fragments. The SLAU_CSIC{} pattern is embedded somewhere in those bytes.\n\nDownload memdump.bin and use the strings command or a hex editor to search for the flag. Try different character encodings and minimum string lengths if the default search doesn't find it immediately.",
                'flag' => 'SLAU_CSIC{memory_forensics_expert}',
                'points' => 500,
                'difficulty' => 'hard',
                'category' => 'digital-forensics',
                'tags' => ['memory', 'strings', 'forensics'],
                'hint' => 'Run `strings memdump.bin` or search for the SLAU_CSIC{} pattern in a hex editor.',
                'sort_order' => 12,
                'files' => ['memdump.bin'],
            ],
            [
                'title' => 'File Carving',
                'slug' => 'file-carving-v2',
                'description' => "Files inside files are like hidden compartments in a wooden desk — invisible to the eye, but obvious to anyone who checks the weight.\n\nJohnMary created a binary container that holds an embedded ZIP archive within its structure. The container file appears to be a single binary, but beneath its surface lies a complete archive with the flag.\n\nDownload container.bin and use binwalk -e to detect and extract the embedded files. Binwalk scans for file signatures — magic bytes that mark where one file type ends and another begins — then carves them out automatically.",
                'flag' => 'SLAU_CSIC{binwalk_file_carving}',
                'points' => 600,
                'difficulty' => 'hard',
                'category' => 'digital-forensics',
                'tags' => ['binwalk', 'file-carving', 'zip'],
                'hint' => 'Use `binwalk -e container.bin` to detect and extract the embedded ZIP archive.',
                'sort_order' => 13,
                'files' => ['container.bin'],
            ],
            [
                'title' => 'Log Correlation',
                'slug' => 'log-correlation-v2',
                'description' => "A single log tells you what happened; three logs together tell you the whole story — who, how, and what comes next.\n\nShakira's server was compromised, and she pulled three log files before shutting it down: auth.log recording login attempts, access.log tracking HTTP requests, and cron.log documenting scheduled tasks. Each log tells part of the story, but correlating timestamps across all three reveals the complete attack timeline.\n\nDownload auth.log, access.log, and cron.log. Match timestamps across files to reconstruct the attack chain: identify the attacker's IP address, determine the method of entry, and find the persistence mechanism they installed.",
                'flag' => 'SLAU_CSIC{incident_response_timeline}',
                'points' => 500,
                'difficulty' => 'hard',
                'category' => 'digital-forensics',
                'tags' => ['logs', 'correlation', 'incident-response'],
                'hint' => 'Match timestamps across auth.log, access.log, and cron.log to reconstruct the attack timeline.',
                'sort_order' => 14,
                'files' => ['auth.log', 'access.log', 'cron.log'],
            ],
            [
                'title' => 'Hex Analysis',
                'slug' => 'hex-analysis-v2',
                'description' => "Beneath every binary file lies a hexadecimal landscape — and some of those hex values decode into something worth reading.\n\nPatience left the flag encoded in hex within a binary file. The data is hidden at a specific offset, mixed in with legitimate binary content. Your job is to locate the hex-encoded ASCII and decode it back to readable text.\n\nDownload binary.bin and examine it with xxd, a hex editor, or Python's binascii module. Search for hex patterns that decode to printable ASCII characters — the flag will be encoded as consecutive hex bytes.",
                'flag' => 'SLAU_CSIC{hex_editor_precision}',
                'points' => 400,
                'difficulty' => 'medium',
                'category' => 'digital-forensics',
                'tags' => ['hex', 'xxd', 'binary'],
                'hint' => 'Use `xxd` or a hex editor to find hex-encoded ASCII patterns, then decode them to text.',
                'sort_order' => 15,
                'files' => ['binary.bin'],
            ],
            [
                'title' => 'Email Parse',
                'slug' => 'email-parse-v2',
                'description' => "Email is more than what you see in your inbox — headers reveal origins, MIME boundaries expose hidden parts, and image metadata whispers secrets.\n\nArafat intercepted a MIME-formatted email that appears to contain a simple image attachment. But emails are layered documents: headers carry routing information and sender metadata, MIME boundaries define content sections, and image files can embed comments in their metadata.\n\nDownload email.eml and examine the full structure. Parse the headers for routing clues, inspect the MIME boundaries for hidden content, and analyze the embedded image's metadata for the flag.",
                'flag' => 'SLAU_CSIC{email_forensics_headers}',
                'points' => 500,
                'difficulty' => 'hard',
                'category' => 'digital-forensics',
                'tags' => ['email', 'mime', 'metadata'],
                'hint' => 'Examine the full MIME structure, headers, and image metadata/comments in the .eml file.',
                'sort_order' => 16,
                'files' => ['email.eml'],
            ],
            [
                'title' => 'ZIP Password',
                'slug' => 'zip-password-v2',
                'description' => "A six-digit password has only a million combinations — that sounds like a lot until a computer tries them all in seconds.\n\nShafic password-protected a ZIP archive with a 6-digit numeric code. The password is short enough to brute-force but long enough to make manual guessing impossible. You need automation to crack it.\n\nDownload protected.zip and use a tool like John the Ripper, fcrackzip, or a Python script to try every combination from 000000 to 999999. Once you crack the password, extract the archive to find the flag.",
                'flag' => 'SLAU_CSIC{zip_password_cracked}',
                'points' => 500,
                'difficulty' => 'hard',
                'category' => 'digital-forensics',
                'tags' => ['zip', 'password', 'brute-force'],
                'hint' => 'Brute-force the 6-digit numeric password with a script or tool like John the Ripper.',
                'sort_order' => 17,
                'files' => ['protected.zip'],
            ],
            [
                'title' => 'Disk Recovery',
                'slug' => 'disk-recovery-v2',
                'description' => "Deleted files don't vanish — they leave ghosts in the filesystem, waiting for someone who knows how to summon them back.\n\nMirembe created a FAT12 disk image with a deliberately deleted file. FAT12 doesn't overwrite deleted data immediately — it simply marks the space as available, leaving the original bytes intact until something new writes over them.\n\nDownload disk.img and use forensic recovery tools or strings to locate the deleted file's data. FAT12's simplicity makes recovery straightforward: the file's content is still sitting in the unallocated space, waiting to be read.",
                'flag' => 'SLAU_CSIC{deleted_file_recovery}',
                'points' => 600,
                'difficulty' => 'hard',
                'category' => 'digital-forensics',
                'tags' => ['disk', 'fat12', 'deleted-files'],
                'hint' => 'Use `strings` or forensic recovery tools on the FAT12 image — deleted data is still in unallocated space.',
                'sort_order' => 18,
                'files' => ['disk.img'],
            ],
            [
                'title' => 'Stego PNG',
                'slug' => 'stego-png-v2',
                'description' => "An image hides its secrets in the bits you'd never think to look at — the least significant ones that your eyes dismiss as noise.\n\nKevin embedded the flag using LSB steganography in a PNG image. Each pixel's red channel has its least significant bit replaced with one bit of the flag data. To the eye, the image looks unchanged — but those tiny modifications carry the entire message.\n\nDownload stego.png and write a Python script using Pillow or numpy. Read the least significant bit of the red channel from each pixel, concatenate them into bytes, and decode the flag from the resulting bit stream.",
                'flag' => 'SLAU_CSIC{lsb_steganography_mastered}',
                'points' => 600,
                'difficulty' => 'hard',
                'category' => 'digital-forensics',
                'tags' => ['steganography', 'lsb', 'png'],
                'hint' => 'Write a Python script to extract the least significant bit from the red channel of each pixel.',
                'sort_order' => 19,
                'files' => ['stego.png'],
            ],
            [
                'title' => 'DNS Exfil',
                'slug' => 'dns-exfil-v2',
                'description' => "DNS queries are the postal service of the internet — and when someone stuffs secrets into the address labels, the postman delivers your flag right to your screen.\n\nOpiyo captured tcpdump output showing DNS exfiltration in action. An attacker is encoding stolen data as subdomain labels in DNS queries to an external server. Each query looks like a normal DNS lookup, but the subdomain portion contains encoded data.\n\nDownload capture.txt and analyze the DNS query names. Identify the suspicious queries, extract the subdomain labels, and reconstruct the exfiltrated data by decoding the encoded portions.",
                'flag' => 'SLAU_CSIC{dns_exfiltration_detected}',
                'points' => 700,
                'difficulty' => 'expert',
                'category' => 'digital-forensics',
                'tags' => ['dns', 'exfiltration', 'tcpdump'],
                'hint' => 'Extract subdomain labels from DNS queries and decode the encoded data within them.',
                'sort_order' => 20,
                'files' => ['capture.txt'],
            ],

            // ── CRYPTOGRAPHY (Category 16) ──
            [
                'title' => 'RSA Fermat',
                'slug' => 'rsa-fermat-v2',
                'description' => "RSA's security rests on factoring being hard — but when two primes are suspiciously close together, Fermat's method makes it embarrassingly easy.\n\nJohnMary generated an RSA keypair where p and q were chosen too close together. The mathematical structure of close primes creates a pattern that Fermat factorization exploits: instead of trying billions of potential factors, you only need to test a few hundred.\n\nDownload rsa.txt for the RSA parameters — n, e, and c. Implement Fermat factorization to split n into p and q, then compute the private key and decrypt the flag.",
                'flag' => 'SLAU_CSIC{fermat_factorization_breaks_rsa}',
                'points' => 600,
                'difficulty' => 'hard',
                'category' => 'cryptography',
                'tags' => ['rsa', 'fermat', 'factorization'],
                'hint' => 'Use Fermat factorization when p and q are close together — iterate a and b until a² - n is a perfect square.',
                'sort_order' => 21,
                'files' => ['rsa.txt'],
            ],
            [
                'title' => 'Two-Time Pad',
                'slug' => 'two-time-pad-v2',
                'description' => "A one-time pad is unbreakable — unless someone reuses the pad. Then it becomes two broken ciphers waiting to be merged.\n\nShakira encrypted two messages with the same XOR key. She gave you one plaintext and both ciphertexts. The key that protects each message individually becomes the weapon that breaks them both when reused.\n\nDownload known.txt (the known plaintext), msg1.enc, and msg2.enc. XOR the two ciphertexts together — the key cancels out, leaving you with the XOR of the two plaintexts. Use the known plaintext to recover the key, then decrypt the second message.",
                'flag' => 'SLAU_CSIC{xor_two_time_pad_break}',
                'points' => 500,
                'difficulty' => 'hard',
                'category' => 'cryptography',
                'tags' => ['xor', 'two-time-pad', 'stream-cipher'],
                'hint' => 'XOR the two ciphertexts together — the shared key cancels out, revealing plaintext₁ ⊕ plaintext₂.',
                'sort_order' => 22,
                'files' => ['known.txt', 'msg1.enc', 'msg2.enc'],
            ],
            [
                'title' => 'Hash Extension',
                'slug' => 'hash-ext-v2',
                'description' => "MD5 lets you extend a message without knowing the secret — if you know how the hash padding works, you can forge any authentication token.\n\nPatience built an authentication system that signs data with MD5(secret || data). The server verifies the signature by recomputing the hash with a secret prefix you're never supposed to know. But MD5's Merkle-Damgård construction has a structural weakness: you can extend a message and compute a valid hash for the extension without knowing the secret.\n\nGiven: original data = 'user=guest&role=user', original hash, and secret length = 16 bytes. Goal: forge a valid hash for data + '&admin=true'. Download hashext.txt and use hash length extension to compute the forged signature.",
                'flag' => 'SLAU_CSIC{md5_length_extension_vulnerability}',
                'points' => 700,
                'difficulty' => 'expert',
                'category' => 'cryptography',
                'tags' => ['md5', 'length-extension', 'hash'],
                'hint' => 'Use a hash length extension tool to forge a valid MD5(secret || data || extension) without knowing the secret.',
                'sort_order' => 23,
                'files' => ['hashext.txt'],
            ],
            [
                'title' => 'Padding Oracle',
                'slug' => 'padding-oracle-v2',
                'description' => "When a server tells you whether your padding is valid after decryption, it's handing you a byte-by-byte decryption oracle — one question at a time.\n\nMirembe set up an AES-CBC decryption oracle. You send it ciphertext, it decrypts and checks the padding, then tells you yes or no. That single bit of information — valid or invalid padding — is enough to decrypt any message, one byte at a time, through a clever adaptive attack.\n\nDownload oracle.py for the oracle implementation and ciphertext.bin for the encrypted flag. The key and IV are embedded in the source. Implement a padding oracle attack to decrypt the ciphertext byte by byte without ever knowing the encryption key.",
                'flag' => 'SLAU_CSIC{padding_oracle_exploited}',
                'points' => 800,
                'difficulty' => 'expert',
                'category' => 'cryptography',
                'tags' => ['aes', 'cbc', 'padding-oracle'],
                'hint' => 'Send modified ciphertext to the oracle, observe valid/invalid padding responses, and decrypt byte by byte.',
                'sort_order' => 24,
                'files' => ['oracle.py', 'ciphertext.bin'],
            ],
            [
                'title' => 'ECB Penguin',
                'slug' => 'ecb-penguin-v2',
                'description' => "ECB mode is like a fingerprint scanner for data — identical inputs produce identical outputs, and that pattern reveals the structure of the message.\n\nShafic encrypted data using AES in ECB (Electronic Codebook) mode. Unlike other modes, ECB encrypts each block independently, so identical plaintext blocks produce identical ciphertext blocks. This creates visible patterns in the encrypted output that reveal message structure.\n\nDownload ecb_data.txt and analyze the block patterns. Group the ciphertext into equal-sized blocks, identify which blocks repeat, and use the pattern to determine the message structure and decode the flag.",
                'flag' => 'SLAU_CSIC{ecb_penguin_pattern_recognition}',
                'points' => 500,
                'difficulty' => 'hard',
                'category' => 'cryptography',
                'tags' => ['aes', 'ecb', 'block-cipher'],
                'hint' => 'In ECB mode, identical plaintext blocks produce identical ciphertext — group and compare blocks.',
                'sort_order' => 25,
                'files' => ['ecb_data.txt'],
            ],
            [
                'title' => 'Substitution',
                'slug' => 'sub-cipher-v2',
                'description' => "Frequency analysis is the oldest trick in the cryptanalyst's book — and it still works against ciphers that simply shuffle letters.\n\nArafat encrypted a message using a monoalphabetic substitution cipher — each letter maps to a fixed different letter. The encryption is consistent throughout the entire message, which means letter frequencies survive the transformation intact.\n\nDownload cipher.txt and analyze the letter frequencies. English text has predictable patterns: 'E' is most common, 'THE' appears frequently, double letters signal common digraphs. Map the most frequent ciphertext letters to their English equivalents to decode the flag.",
                'flag' => 'SLAU_CSIC{frequency_analysis_works}',
                'points' => 400,
                'difficulty' => 'medium',
                'category' => 'cryptography',
                'tags' => ['substitution', 'frequency-analysis', 'classical'],
                'hint' => 'Analyze letter frequencies against English averages — the most common ciphertext letter is likely E.',
                'sort_order' => 26,
                'files' => ['cipher.txt'],
            ],
            [
                'title' => 'Diffie-Hellman',
                'slug' => 'dh-weak-v2',
                'description' => "Diffie-Hellman's strength lives in the difficulty of discrete logarithms — but when someone picks a private key small enough to count on your fingers, brute force finishes what mathematics started.\n\nOpiyo performed a Diffie-Hellman key exchange with a server, but the server chose a private key that's far too small. The public parameters — generator g, prime p, and the server's public value — are all visible, and the private key is small enough to enumerate.\n\nDownload dh.txt for the DH parameters. Brute-force Bob's private key by testing small values, compute the shared secret, and derive the flag from the shared secret.",
                'flag' => 'SLAU_CSIC{dh_weak_private_key}',
                'points' => 600,
                'difficulty' => 'hard',
                'category' => 'cryptography',
                'tags' => ['diffie-hellman', 'brute-force', 'key-exchange'],
                'hint' => 'Brute-force Bob\'s private key by testing small values against the public parameters.',
                'sort_order' => 27,
                'files' => ['dh.txt'],
            ],
            [
                'title' => 'CBC Bit Flip',
                'slug' => 'cbc-bitflip-v2',
                'description' => "CBC mode chains blocks together so that changing one ciphertext block scrambles the next — but a careful bit flip in the right place can reshape the plaintext without knowing the key.\n\nKevin built a server that encrypts user tokens with AES-CBC. The plaintext contains 'user=guest' and you need to change it to 'user=admin'. In CBC mode, flipping a bit in ciphertext block N flips the same bit in plaintext block N+1 after decryption.\n\nDownload server.py for the encryption logic and encrypted.bin for the ciphertext. Identify which ciphertext byte corresponds to the 'g' in 'guest', flip it to 'a', and compute the correct bit changes to produce 'admin' in the decrypted output.",
                'flag' => 'SLAU_CSIC{cbc_bit_flip_attack}',
                'points' => 700,
                'difficulty' => 'expert',
                'category' => 'cryptography',
                'tags' => ['aes', 'cbc', 'bit-flip'],
                'hint' => 'In CBC mode, flipping a bit in ciphertext block N flips the same bit in plaintext block N+1.',
                'sort_order' => 28,
                'files' => ['server.py', 'encrypted.bin'],
            ],
            [
                'title' => 'Lattice Crypto',
                'slug' => 'lattice-crypto-v2',
                'description' => "When hidden numbers lurk behind modular equations, lattice reduction pulls them into the light — one basis vector at a time.\n\nShakira set up a Hidden Number Problem: 20 equations where each equation hides a secret number modulo a large prime. The equations look random, but the hidden numbers follow a pattern that lattice reduction can exploit.\n\nDownload lattice.txt for the challenge parameters. Build a lattice from the equations, apply the LLL algorithm to find short vectors, and extract the hidden number. The flag encodes the secret as hex characters.",
                'flag' => 'SLAU_CSIC{lattice_reduction_cryptanalysis}',
                'points' => 800,
                'difficulty' => 'expert',
                'category' => 'cryptography',
                'tags' => ['lattice', 'lll', 'hidden-number'],
                'hint' => 'Build a lattice matrix from the equations and apply LLL reduction to find short vectors.',
                'sort_order' => 29,
                'files' => ['lattice.txt'],
            ],
            [
                'title' => 'PRNG Prediction',
                'slug' => 'prng-predict-v2',
                'description' => "A Linear Congruential Generator is a drumbeat of predictable randomness — once you hear enough beats, you can predict every note that follows.\n\nJohnMary generated 100 outputs from a Linear Congruential Generator and used those outputs to encrypt the flag. The LCG follows the formula x[n+1] = (a * x[n] + c) % m, and with enough outputs, you can recover the parameters a, c, and m by solving a system of equations.\n\nDownload output.txt for the LCG outputs and prng.py for the encryption logic. Recover the LCG parameters, predict the next outputs, and decrypt the flag using the predicted keystream.",
                'flag' => 'SLAU_CSIC{prng_prediction_mastered}',
                'points' => 600,
                'difficulty' => 'hard',
                'category' => 'cryptography',
                'tags' => ['prng', 'lcg', 'predict'],
                'hint' => 'Recover the LCG parameters (a, c, m) by solving equations with consecutive outputs.',
                'sort_order' => 30,
                'files' => ['output.txt', 'prng.py'],
            ],
        ];

        foreach ($challenges as $data) {
            $category = $categories[$data['category']] ?? $categories['reverse-engineering'];

            $existing = CtfChallenge::where('ctf_competition_id', $competition->id)
                ->where('slug', $data['slug'])
                ->exists();

            if ($existing) {
                $this->command->info("  Skipping: {$data['title']} (already exists)");

                continue;
            }

            $challenge = CtfChallenge::create([
                'ctf_competition_id' => $competition->id,
                'ctf_category_id' => $category->id,
                'title' => $data['title'],
                'slug' => $data['slug'],
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

            if (isset($data['files'])) {
                foreach ($data['files'] as $fileName) {
                    $storedPath = 'ctf/linux-masters/'.str_pad($data['sort_order'], 2, '0', STR_PAD_LEFT).'/'.$fileName;
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
        $this->command->info("\n Competition: {$competition->title}");
        $this->command->info("  Challenges: {$total}");
        $this->command->info("  Total points: {$points}");
        $this->command->info("  URL: /ctf/{$competition->slug}");
    }
}
