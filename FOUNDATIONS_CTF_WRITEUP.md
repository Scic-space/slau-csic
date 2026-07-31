# SLAU CSIC Linux Foundations CTF — Solution Writeup

> **35 Challenges | 10,875 Points | Linux Fundamentals**
>
> This writeup explains how to solve every challenge in the Linux Foundations CTF.
> Each section covers the concept, the approach, and the exact commands used.

---

## Tier 1: Beginner (50–100 pts)

---

### Challenge 1 — First Steps (50 pts)
**Category:** Navigation & Basics | **Difficulty:** Easy
**Concept:** `cat` reads file contents. The most basic Linux skill.

**Solution:**
```bash
cat flag.txt
```
**Flag:** `SLAU_CSIC{welcome_to_linux}`

---

### Challenge 2 — Hidden in Plain Sight (75 pts)
**Category:** Navigation & Basics | **Difficulty:** Easy
**Concept:** Files starting with `.` are hidden from `ls`. Use `ls -a` to see them.

**Solution:**
```bash
tar -xzf hide_and_seek.tar.gz
ls -a
cat .hidden_flag
```
**Flag:** `SLAU_CSIC{hidden_files_discovered}`

---

### Challenge 3 — Read the Signs (100 pts)
**Category:** File Operations | **Difficulty:** Easy
**Concept:** `file` inspects actual file contents (magic bytes), not the extension. `strings` extracts readable text from binaries.

**Solution:**
```bash
file mystery.jpg          # Reports: PNG image data (not JPEG!)
strings mystery.jpg | grep SLAU_CSIC
```
**Flag:** `SLAU_CSIC{file_type_detection}`

---

### Challenge 4 — Permission Denied (100 pts)
**Category:** Permissions & Ownership | **Difficulty:** Easy
**Concept:** `chmod` changes file permissions. A file with `000` permissions is completely inaccessible.

**Solution:**
```bash
ls -la secret.txt          # Shows: ---------- (000 permissions)
cat secret.txt             # Fails: Permission denied
chmod 644 secret.txt       # Make it readable
cat secret.txt
```
**Flag:** `SLAU_CSIC{permissions_mastered}`

---

## Tier 2: Easy (125–175 pts)

---

### Challenge 5 — Word Hunter (125 pts)
**Category:** Text Processing | **Difficulty:** Easy
**Concept:** `grep` searches file contents for patterns — like Ctrl+F for the terminal.

**Solution:**
```bash
grep SLAU_CSIC access.log
```
**Flag:** `SLAU_CSIC{grep_is_your_friend}`

---

### Challenge 6 — Line by Line (150 pts)
**Category:** Text Processing | **Difficulty:** Easy
**Concept:** `sed -n 'Xp'` prints line X. `head` and `tail` can also extract specific lines via piping.

**Solution:**
```bash
sed -n '4217p' data.txt
```
**Alternative:**
```bash
head -n 4217 data.txt | tail -n 1
```
**Flag:** `SLAU_CSIC{line_4217_extracted}`

---

### Challenge 7 — File Fingerprint (150 pts)
**Category:** Analysis & Forensics | **Difficulty:** Easy
**Concept:** MD5/SHA-256 checksums create unique fingerprints. Identical files have identical checksums.

**Solution:**
```bash
md5sum original.txt copy_a.txt copy_b.txt
```
```
b809e108...  original.txt
b809e108...  copy_a.txt      ← MATCH
e56428c0...  copy_b.txt
```
`copy_a.txt` has the same checksum as `original.txt` — it's the exact copy.
```bash
cat copy_a.txt
```
**Flag:** `SLAU_CSIC{checksum_verified}`

---

### Challenge 8 — String Theory (175 pts)
**Category:** Analysis & Forensics | **Difficulty:** Easy
**Concept:** `strings` extracts readable text (4+ printable characters) from any file, including binaries.

**Solution:**
```bash
strings mystery.bin | grep SLAU_CSIC
```
**Flag:** `SLAU_CSIC{strings_reveals_all}`

---

## Tier 3: Medium (200–300 pts)

---

### Challenge 9 — Pipe Dream (200 pts)
**Category:** Text Processing | **Difficulty:** Medium
**Concept:** Pipes (`|`) chain commands — output of one becomes input of the next.

**Solution:**
```bash
grep "FLAG" raw_data.txt | grep SLAU_CSIC
```
**Flag:** `SLAU_CSIC{pipes_unleashed}`

---

### Challenge 10 — Sort & Destroy (200 pts)
**Category:** Text Processing | **Difficulty:** Medium
**Concept:** `sort` orders lines; `awk` extracts columns. Together they find the highest value in structured data.

**Solution:**
```bash
sort -t, -k3 -rn users.csv | head -1
```
This sorts the CSV by column 3 (score) in reverse numeric order and shows the top line.

Alternatively, look at the admin row:
```bash
tail -1 users.csv
```
**Flag:** `SLAU_CSIC{sorted_and_found}`

---

### Challenge 11 — Archive Explorer (225 pts)
**Category:** File Operations | **Difficulty:** Medium
**Concept:** `tar -xzf` extracts `.tar.gz` archives. Navigate the directory tree inside.

**Solution:**
```bash
tar -xzf challenge.tar.gz
find . -name "flag.txt" -o -name "*flag*"
cat level1/level2/level3/flag.txt
```
**Flag:** `SLAU_CSIC{archive_navigator}`

---

### Challenge 12 — Log Detective (275 pts)
**Category:** Analysis & Forensics | **Difficulty:** Medium
**Concept:** Search across multiple log files to find anomalies. `grep -r` or `grep` with file arguments searches multiple files.

**Solution:**
```bash
grep SLAU_CSIC auth.log access.log error.log
```
The flag is in `auth.log`.
**Flag:** `SLAU_CSIC{log_analyst}`

---

### Challenge 13 — Hex Peek (300 pts)
**Category:** Analysis & Forensics | **Difficulty:** Medium
**Concept:** Hex dumps represent binary data as hex characters. `xxd` creates and reverses hex dumps.

**Solution:**
```bash
strings encoded.bin | grep SLAU_CSIC
# Or view the hexdump:
cat hexdump.txt
```
The flag bytes are visible in the ASCII column of the hex dump.
**Flag:** `SLAU_CSIC{hex_reader_pro}`

---

### Challenge 14 — Script Reader (350 pts)
**Category:** Scripting & Networking | **Difficulty:** Hard
**Concept:** Read bash scripts to understand their logic, or run them to see output.

**Solution:**
```bash
cat process.sh              # Read the script logic
cat data_input.txt          # See the input data
bash process.sh             # Run it
```
The script greps for "CTF" in the data file, sorts the matches, and extracts the 3rd colon-delimited field.

Manual trace:
```bash
grep "CTF" data_input.txt | sort | awk -F: '{print $3}' | head -n 1
```
**Flag:** `SLAU_CSIC{script_master}`

---

### Challenge 15 — Process Inspector (350 pts)
**Category:** Scripting & Networking | **Difficulty:** Hard
**Concept:** Every running process has a PID. `/proc/[PID]/` exposes its details. `ps aux` lists all processes.

**Solution:**
```bash
ps aux | grep -i flag
# Or search through the process dump:
grep SLAU_CSIC process_dump.txt
```
The flag is embedded in a process's command-line arguments.
**Flag:** `SLAU_CSIC{ps_aux_grep_mastery}`

---

### Challenge 16 — Network Navigator (400 pts)
**Category:** Scripting & Networking | **Difficulty:** Hard
**Concept:** `curl` fetches data from URLs. HTTP APIs return structured responses.

**Solution:**
```bash
grep SLAU_CSIC capture.pcap.txt
# Or look at the HTTP response in the capture:
grep -A5 "flag" capture.pcap.txt
```
The capture shows an HTTP response from an internal API containing the flag.
**Flag:** `SLAU_CSIC{curl_and_http_decoded}`

---

### Challenge 17 — Compiled Secrets (500 pts)
**Category:** Analysis & Forensics | **Difficulty:** Hard
**Concept:** Compiled binaries retain readable strings in their data sections. `strings` and `objdump` extract them.

**Solution:**
```bash
strings secret_program | grep SLAU_CSIC
# Or for deeper inspection:
objdump -s -j .data secret_program
```
**Flag:** `SLAU_CSIC{strings_and_objdump}`

---

## Tier 4: Expert (500 pts each)

---

### Challenge 18 — Multi-Stage Maze (500 pts)
**Category:** Analysis & Forensics | **Difficulty:** Insane
**Concept:** Multi-stage challenges chain encoding methods. Each decoded message reveals instructions for the next stage.

**Solution:**
```bash
cat stage1.txt              # Shows base64: U0xBVV9DU0lDe21hemVfY29tcGxldGVkfQ==
echo "U0xBVV9DU0lDe21hemVfY29tcGxldGVkfQ==" | base64 -d
```
**Flag:** `SLAU_CSIC{maze_completed}`

---

### Challenge 19 — The Gauntlet (500 pts)
**Category:** Analysis & Forensics | **Difficulty:** Insane
**Concept:** Combine multiple skills: file analysis, strings, hex inspection, pattern recognition.

**Solution:**
```bash
cat hidden.txt              # Flag is in plaintext here
# Also try other tools:
file challenge_data.bin     # It's an OpenPGP key (decoy)
strings challenge_data.bin  # No flag inside
```
**Flag:** `SLAU_CSIC{gauntlet_conquered}`

---

## Tier 5: System Administration (250–500 pts)

---

### Challenge 20 — The Great Search (250 pts)
**Category:** File Operations | **Difficulty:** Medium
**Concept:** `find` recursively searches directories by name, type, size, or other attributes.

**Solution:**
```bash
tar -xzf find_challenge.tar.gz
find . -name "secret*"
cat dir3/secret_flag.txt
```
**Alternative:**
```bash
find . -name "*.txt" -type f
```
**Flag:** `SLAU_CSIC{find_command_power}`

---

### Challenge 21 — System Recon (250 pts)
**Category:** Analysis & Forensics | **Difficulty:** Medium
**Concept:** System information commands: `uname -a`, `cat /etc/os-release`, `free -h`, `uptime`.

**Solution:**
```bash
cat system_report.txt
```
The report contains kernel version, memory, and uptime. The flag is on a specific line.
**Flag:** `SLAU_CSIC{uname_a_cat_os_release}`

---

### Challenge 22 — Environment Explorer (250 pts)
**Category:** Analysis & Forensics | **Difficulty:** Medium
**Concept:** Environment variables store configuration, credentials, and sometimes secrets.

**Solution:**
```bash
grep -i "flag\|secret\|key" env_dump.txt
# Or just read the file:
cat env_dump.txt
```
**Flag:** `SLAU_CSIC{env_vars_leak_secrets}`

---

### Challenge 23 — Disk Detective (300 pts)
**Category:** Analysis & Forensics | **Difficulty:** Medium
**Concept:** `du` shows disk usage. `find -size` locates files by size.

**Solution:**
```bash
cat disk_report.txt
```
The report reveals a hidden large file path. Read the flag from it.
**Flag:** `SLAU_CSIC{du_find_large_files}`

---

### Challenge 24 — Link Master (300 pts)
**Category:** File Operations | **Difficulty:** Medium
**Concept:** Symlinks (`l` prefix in `ls -la`) point to a path. Hard links share the same inode as the original.

**Solution:**
```bash
tar -xzf link_challenge.tar.gz
ls -la                      # Spot symlinks (l prefix, -> arrow)
find . -name "flag*"        # Find the original buried file
stat legacy/archive/secret/flag_content.txt   # Note the inode number
stat link_delta.txt         # Same inode = hard link
cat link_delta.txt
```
Key insight: `link_delta.txt` looks like a regular file but shares inode with the deep original. Symlinks point to decoys.

**Flag:** `SLAU_CSIC{symlinks_and_hard_links}`

---

### Challenge 25 — Temp Files (350 pts)
**Category:** File Operations | **Difficulty:** Hard
**Concept:** `find -mmin` filters files by modification time. Essential for forensic analysis.

**Solution:**
```bash
cat temp_files.txt
```
The file lists timestamps and file paths. The secret was created before 07:30.
**Flag:** `SLAU_CSIC{find_time_based_filtering}`

---

## Tier 6: Encoding & Decoding (350–500 pts)

---

### Challenge 26 — Caesar Salad (350 pts)
**Category:** Text Processing | **Difficulty:** Hard
**Concept:** ROT13 shifts each letter 13 positions. It's symmetric — applying twice returns the original.

**Solution:**
```bash
cat encoded_message.txt
echo "FYNH_PFVP{pnrfne_pvcure_penpxrq}" | tr 'A-Za-z' 'N-ZA-Mn-za-m'
```
**Flag:** `SLAU_CSIC{caesar_cipher_cracked}`

---

### Challenge 27 — Log Parser (400 pts)
**Category:** Text Processing | **Difficulty:** Hard
**Concept:** `awk` splits lines into fields. Combined with `sort | uniq -c`, it counts occurrences.

**Solution:**
```bash
awk '{print $4}' access_logs.txt | sort | uniq -c | sort -rn | head -5
```
The most frequent endpoint is `/api/submit-flag`.
```bash
grep SLAU_CSIC access_logs.txt
```
**Flag:** `SLAU_CSIC{sort_uniq_count_mastery}`

---

### Challenge 28 — Regex Raider (400 pts)
**Category:** Text Processing | **Difficulty:** Hard
**Concept:** `grep -E` enables extended regex: `+`, `{}`, `[]`, `^`, `$`.

**Solution:**
```bash
grep -E "ERROR.*172\.16\.[0-9]+\.[0-9]+.*port=3306.*bytes=[4-9][0-9]{3,}" network_log.txt
```
This matches ERROR entries from 172.16.x.x to port 3306 with 4000+ bytes.
```bash
grep SLAU_CSIC network_log.txt
```
**Flag:** `SLAU_CSIC{grep_extended_regex_prowess}`

---

### Challenge 29 — Permission Audit (400 pts)
**Category:** Permissions & Ownership | **Difficulty:** Hard
**Concept:** SUID binaries (`s` in owner execute) run as root. `rwsrwxrwx` is abnormally permissive.

**Solution:**
```bash
cat suid_report.txt
```
Look for `rwsrwxrwx` permissions — that's the vulnerable binary.
```bash
grep "rwsrwxrwx" suid_report.txt
grep SLAU_CSIC suid_report.txt
```
**Flag:** `SLAU_CSIC{find_suid_abnormal_permissions}`

---

### Challenge 30 — User Enumeration (450 pts)
**Category:** Analysis & Forensics | **Difficulty:** Hard
**Concept:** `/etc/passwd` has 7 colon-separated fields. Parse with `awk -F:`.

**Solution:**
```bash
awk -F: '$7 != "/usr/sbin/nologin" && $7 != "/bin/false"' passwd_audit.txt | wc -l
awk -F: '{print $3}' passwd_audit.txt | sort | uniq -c | sort -rn | head -5
awk -F: '$3 == 1000' passwd_audit.txt
grep SLAU_CSIC passwd_audit.txt
```
**Flag:** `SLAU_CSIC{awk_passwd_field_parsing}`

---

### Challenge 31 — Cron Inspector (450 pts)
**Category:** Scripting & Networking | **Difficulty:** Hard
**Concept:** Crontab format: `minute hour day month weekday command`. Read entries to find scheduled tasks.

**Solution:**
```bash
cat crontab.txt
```
Find the `rotate_flags.sh` entry and read its arguments.
```bash
grep "rotate_flags" crontab.txt
grep SLAU_CSIC crontab.txt
```
**Flag:** `SLAU_CSIC{cron_scheduling_time_fields}`

---

### Challenge 32 — SQLite Sleuth (500 pts)
**Category:** Analysis & Forensics | **Difficulty:** Hard
**Concept:** `sqlite3` lets you query SQLite databases from the terminal.

**Solution:**
```bash
sqlite3 users.db ".tables"                    # List tables
sqlite3 users.db ".schema flags"              # See structure
sqlite3 users.db "SELECT * FROM flags;"       # List all flags
```
The `flags` table contains hidden flags. Query:
```bash
sqlite3 users.db "SELECT flag_value FROM flags WHERE flag_name='First Blood';"
```
**Flag:** `SLAU_CSIC{sqlite_select_where}`

---

### Challenge 33 — Base64 Breaker (500 pts)
**Category:** Text Processing | **Difficulty:** Hard
**Concept:** Base64 encodes binary as ASCII text. `base64 -d` decodes it.

**Solution:**
```bash
cat encoded_data.txt
echo "U0xBVV9DU0lDe2Jhc2U2NF9kZWNvZGVfbWFzdGVyZWR9" | base64 -d
```
**Flag:** `SLAU_CSIC{base64_decode_mastered}`

---

### Challenge 34 — Script Debugger (500 pts)
**Category:** Scripting & Networking | **Difficulty:** Hard
**Concept:** Common bash bugs: unquoted variables, wrong comparison operators, array expansion.

**Solution:**
```bash
cat broken_script.sh
cat hint.txt
```
The hint identifies 3 bugs:
1. Unquoted `$1` — word splitting on arguments
2. `==` instead of `=` in `[ ]` test (POSIX uses `=`)
3. Unquoted `${array[@]}` — skips elements with spaces

```bash
grep SLAU_CSIC hint.txt
```
**Flag:** `SLAU_CSIC{bash_debugging_skills}`

---

### Challenge 35 — Netstat Ninja (500 pts)
**Category:** Scripting & Networking | **Difficulty:** Hard
**Concept:** `netstat` and `ss` reveal active connections, listening ports, and processes.

**Solution:**
```bash
cat netstat_output.txt
grep "8080" netstat_output.txt
```
Find the process listening on port 8080 and its connected client IP.
```bash
grep SLAU_CSIC netstat_output.txt
```
**Flag:** `SLAU_CSIC{netstat_ss_network_analysis}`

---

## Summary

| # | Challenge | Points | Flag | Key Command |
|---|-----------|--------|------|-------------|
| 1 | First Steps | 50 | `SLAU_CSIC{welcome_to_linux}` | `cat` |
| 2 | Hidden in Plain Sight | 75 | `SLAU_CSIC{hidden_files_discovered}` | `ls -a` |
| 3 | Read the Signs | 100 | `SLAU_CSIC{file_type_detection}` | `file`, `strings` |
| 4 | Permission Denied | 100 | `SLAU_CSIC{permissions_mastered}` | `chmod` |
| 5 | Word Hunter | 125 | `SLAU_CSIC{grep_is_your_friend}` | `grep` |
| 6 | Line by Line | 150 | `SLAU_CSIC{line_4217_extracted}` | `sed -n 'Xp'` |
| 7 | File Fingerprint | 150 | `SLAU_CSIC{checksum_verified}` | `md5sum` |
| 8 | String Theory | 175 | `SLAU_CSIC{strings_reveals_all}` | `strings` |
| 9 | Pipe Dream | 200 | `SLAU_CSIC{pipes_unleashed}` | `\|` pipes |
| 10 | Sort & Destroy | 200 | `SLAU_CSIC{sorted_and_found}` | `sort`, `awk` |
| 11 | Archive Explorer | 225 | `SLAU_CSIC{archive_navigator}` | `tar -xzf` |
| 12 | Log Detective | 275 | `SLAU_CSIC{log_analyst}` | `grep` (multi-file) |
| 13 | Hex Peek | 300 | `SLAU_CSIC{hex_reader_pro}` | `xxd`, `strings` |
| 14 | Script Reader | 350 | `SLAU_CSIC{script_master}` | `bash`, `cat` |
| 15 | Process Inspector | 350 | `SLAU_CSIC{ps_aux_grep_mastery}` | `ps aux`, `grep` |
| 16 | Network Navigator | 400 | `SLAU_CSIC{curl_and_http_decoded}` | `curl`, `grep` |
| 17 | Compiled Secrets | 500 | `SLAU_CSIC{strings_and_objdump}` | `strings`, `objdump` |
| 18 | Multi-Stage Maze | 500 | `SLAU_CSIC{maze_completed}` | `base64 -d` |
| 19 | The Gauntlet | 500 | `SLAU_CSIC{gauntlet_conquered}` | `file`, `strings`, `cat` |
| 20 | The Great Search | 250 | `SLAU_CSIC{find_command_power}` | `find` |
| 21 | System Recon | 250 | `SLAU_CSIC{uname_a_cat_os_release}` | `cat`, `grep` |
| 22 | Environment Explorer | 250 | `SLAU_CSIC{env_vars_leak_secrets}` | `grep`, `cat` |
| 23 | Disk Detective | 300 | `SLAU_CSIC{du_find_large_files}` | `du`, `find` |
| 24 | Link Master | 300 | `SLAU_CSIC{symlinks_and_hard_links}` | `ls -la`, `stat` |
| 25 | Temp Files | 350 | `SLAU_CSIC{find_time_based_filtering}` | `find -mmin` |
| 26 | Caesar Salad | 350 | `SLAU_CSIC{caesar_cipher_cracked}` | `tr` (ROT13) |
| 27 | Log Parser | 400 | `SLAU_CSIC{sort_uniq_count_mastery}` | `awk`, `sort`, `uniq` |
| 28 | Regex Raider | 400 | `SLAU_CSIC{grep_extended_regex_prowess}` | `grep -E` |
| 29 | Permission Audit | 400 | `SLAU_CSIC{find_suid_abnormal_permissions}` | `grep` (permissions) |
| 30 | User Enumeration | 450 | `SLAU_CSIC{awk_passwd_field_parsing}` | `awk -F:` |
| 31 | Cron Inspector | 450 | `SLAU_CSIC{cron_scheduling_time_fields}` | `cat`, `grep` |
| 32 | SQLite Sleuth | 500 | `SLAU_CSIC{sqlite_select_where}` | `sqlite3` |
| 33 | Base64 Breaker | 500 | `SLAU_CSIC{base64_decode_mastered}` | `base64 -d` |
| 34 | Script Debugger | 500 | `SLAU_CSIC{bash_debugging_skills}` | `cat`, code review |
| 35 | Netstat Ninja | 500 | `SLAU_CSIC{netstat_ss_network_analysis}` | `grep`, `netstat` |
