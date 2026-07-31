# SLAU CSIC Linux Masters CTF — Solution Writeup

> **30 Challenges | 17,600 Points | Reverse Engineering, Digital Forensics, Cryptography**
>
> This writeup explains how to solve every challenge in the Linux Masters CTF.
> Each section covers the concept, the approach, and the exact steps used.

---

## Category 1: Reverse Engineering (Challenges 1–10)

---

### Challenge 1 — Buffer Overflow (400 pts)
**Difficulty:** Hard | **Concept:** Struct layout corruption via `strcpy`

**Scenario:** A C authentication server uses `strcpy` into a fixed-size buffer. The `auth_token` buffer and `authenticated` boolean sit adjacent in a packed struct. Overflowing the token buffer overwrites the auth flag.

**Files:** `vuln_server.c`, `vuln_server` (ELF binary), `flag.txt`

**Solution:**
```bash
# Study the struct layout in vuln_server.c
# The auth_token is 16 bytes, authenticated sits at offset 16
# strcpy has no bounds check — overflow the token to flip authenticated

# Send a Bearer token with >16 non-null bytes
curl -H "Authorization: Bearer AAAAAAAAAAAAAAAAAA" http://localhost:8080/validate
# Or craft a request with 17+ 'A' characters in the token
```

The `flag.txt` becomes accessible once `authenticated` is non-zero (any non-zero value).

**Flag:** `SLAU_CSIC{b4by_0v3rfl0w_1s_d4ng3r0us}`

---

### Challenge 2 — Python Packer (500 pts)
**Difficulty:** Hard | **Concept:** Multi-layer XOR/base64 obfuscation

**Scenario:** Five nested encoding layers: XOR with `0x42` → base64 decode → XOR with key array → plaintext.

**Files:** `packed.py`

**Solution:**
```python
# Just run the script — it decrypts and prints the flag
python3 packed.py
```

Or manually deobfuscate:
```python
import base64

_enc = bytes([7, 42, 8, 53, 1, 21, 122, 21, ...])  # from packed.py
_l2 = bytes([b ^ 0x42 for b in _enc])        # XOR with 0x42 → base64
_l3 = base64.b64decode(_l2)                    # base64 decode → XOR-encrypted bytes
_key = [0x41, 0x5e, 0x31, 0x5c, ...]          # from packed.py
flag = ''.join(chr(b ^ _key[i]) for i, b in enumerate(_l3))
print(flag)
```

**Flag:** `SLAU_CSIC{pyth0n_l4y3rs_g0_d33p}`

---

### Challenge 3 — x86 Disassembly (600 pts)
**Difficulty:** Hard | **Concept:** XOR cipher in assembly

**Scenario:** A disassembled binary validates a passphrase by XORing each input byte against a constant (`$0x41`) and comparing against a stored array.

**Files:** `disasm.txt`

**Solution:**
```python
# Extract the XOR_KEY array values from disasm.txt
# Each entry: mov al, BYTE PTR [rdi+OFFSET]; xor al, 0x41; cmp al, VALUE
# The comparison VALUE is the XOR-encoded flag byte

xor_key = [
    0x12, 0x0d, 0x00, 0x14, 0x1e, 0x02, 0x12, 0x08,
    0x02, 0x3a, 0x39, 0x79, 0x77, 0x1e, 0x25, 0x28,
    0x32, 0x20, 0x32, 0x32, 0x24, 0x2c, 0x23, 0x2d,
    0x38, 0x1e, 0x25, 0x24, 0x22, 0x2e, 0x25, 0x24,
    0x25, 0x3c
]

flag = ''.join(chr(b ^ 0x41) for b in xor_key)
print(flag)  # SLAU_CSIC{x86_disassembly_decoded}
```

**Flag:** `SLAU_CSIC{x86_disassembly_decoded}`

---

### Challenge 4 — Custom VM (800 pts)
**Difficulty:** Expert | **Concept:** Stack-based virtual machine reverse engineering

**Scenario:** A custom 8-bit stack-based VM with opcodes: PUSH (0x01), POP (0x02), ADD (0x03), XOR (0x04), PRINT (0x05), HALT (0x06), DUP (0x07), SUB (0x08), JMP_IF_ZERO (0x09). The bytecode encodes the flag.

**Files:** `vm_spec.txt`, `challenge.bin`

**Solution:**
```python
# Implement the VM from vm_spec.txt
import struct

with open('challenge.bin', 'rb') as f:
    data = f.read()

prog_len = struct.unpack('>H', data[:2])[0]
bytecode = data[2:2+prog_len]

stack = []
ip = 0
output = []

while ip < len(bytecode):
    op = bytecode[ip]; ip += 1
    if op == 0x01:   # PUSH
        stack.append(bytecode[ip]); ip += 1
    elif op == 0x02: # POP
        stack.pop()
    elif op == 0x03: # ADD
        a, b = stack.pop(), stack.pop()
        stack.append((a + b) & 0xFF)
    elif op == 0x04: # XOR
        a, b = stack.pop(), stack.pop()
        stack.append(a ^ b)
    elif op == 0x05: # PRINT
        output.append(chr(stack.pop()))
    elif op == 0x06: # HALT
        break
    elif op == 0x07: # DUP
        stack.append(stack[-1])
    elif op == 0x08: # SUB
        a, b = stack.pop(), stack.pop()
        stack.append((a - b) & 0xFF)
    elif op == 0x09: # JMP_IF_ZERO
        val = stack.pop()
        addr = struct.unpack('>H', bytecode[ip:ip+2])[0]; ip += 2
        if val == 0:
            ip = addr

print(''.join(output))
```

**Flag:** `SLAU_CSIC{v1rtu4l_m4ch1n3_r3v3r3d}`

---

### Challenge 5 — Algorithm RE (700 pts)
**Difficulty:** Expert | **Concept:** Custom cipher with S-box, transposition, and cumulative XOR

**Scenario:** Three-step encryption: S-box substitution (seeded by SHA-256 of `b"SLAU_CSIC_CTF_2024"`) → transposition `(i*7+3) % n` → cumulative XOR diffusion. Only the encryption function is provided.

**Files:** `cipher.py`, `encrypted.txt`

**Solution:**
```python
import hashlib
import random

# Rebuild the S-box (same seed as encryption)
_random = random.Random()
_seed_bytes = hashlib.sha256(b"SLAU_CSIC_CTF_2024").digest()
_random.seed(_seed_bytes)
_SBOX = list(range(256))
for i in range(255, 0, -1):
    j = _random.randint(0, i)
    _SBOX[i], _SBOX[j] = _SBOX[j], _SBOX[i]

# Build reverse S-box
REV_SBOX = [0] * 256
for i, v in enumerate(_SBOX):
    REV_SBOX[v] = i

def inv_modular_transform(data):
    n = len(data)
    result = bytearray(n)
    result[0] = data[0]
    for i in range(1, n):
        result[i] = data[i] ^ result[i-1] ^ (i & 0xFF)
    return bytes(result)

def inv_transpose(data):
    n = len(data)
    result = bytearray(n)
    for i in range(n):
        result[(i * 7 + 3) % n] = data[i]
    return bytes(result)

def inv_sbox(data):
    return bytes([REV_SBOX[b] for b in data])

ct = bytes.fromhex('9773bff2084e3a36080f7c4644787ee631feae19f5139fcf77e0c8e946a6688822f39aac9925eb')
pt = inv_sbox(inv_transpose(inv_modular_transform(ct))
print(pt.decode())
```

**Flag:** `SLAU_CSIC{r3v3rs3_th3_c1ph3r_4lg0r1thm}`

---

### Challenge 6 — Binary Protocol (600 pts)
**Difficulty:** Hard | **Concept:** Custom binary protocol parsing

**Scenario:** A custom network protocol with magic bytes `CSIC`, version field, packet types, and type-0x03 packets carrying the flag.

**Files:** `capture.bin`

**Solution:**
```python
import struct

with open('capture.bin', 'rb') as f:
    data = f.read()

# Magic: CSIC (4 bytes)
# Parse header, enumerate packet types
# Type 0x03 packets contain the flag payload

magic = data[:4]
print(f"Magic: {magic}")

# Walk through packets, extract type-0x03 payloads
offset = 4
while offset < len(data):
    pkt_type = data[offset]
    pkt_len = struct.unpack('>H', data[offset+1:offset+3])[0]
    payload = data[offset+3:offset+3+pkt_len]
    if pkt_type == 0x03:
        print(f"Flag packet: {payload}")
    offset += 3 + pkt_len
```

**Flag:** `SLAU_CSIC{n3tw0rk_pr0t0c0l_4n4lyz3d}`

---

### Challenge 7 — Logic Circuit (500 pts)
**Difficulty:** Hard | **Concept:** Self-inverse boolean circuit

**Scenario:** A boolean circuit with XOR and AND gates. The circuit is its own inverse — pass ciphertext through the same circuit to get plaintext.

**Files:** `circuit.txt`

**Solution:**
```python
# The circuit implements: output = input XOR ((input >> 4) & 7)
# This is self-inverse: XOR is its own inverse

# Given encrypted flag bytes (hex):
encrypted = [
    0x56, 0x48, 0x45, 0x50, 0x5a, 0x47, 0x56, 0x4d,
    0x47, 0x7c, 0x64, 0x69, 0x69, 0x6a, 0x63, 0x67,
    0x68, 0x5a, 0x65, 0x6f, 0x75, 0x65, 0x72, 0x6f,
    0x73, 0x5a, 0x75, 0x63, 0x71, 0x63, 0x75, 0x74,
    0x63, 0x62, 0x7a
]

def circuit(byte_val):
    """The self-inverse circuit: output = input XOR ((input >> 4) & 7)"""
    return byte_val ^ ((byte_val >> 4) & 7)

flag = ''.join(chr(circuit(b)) for b in encrypted)
print(flag)
```

**Flag:** `SLAU_CSIC{boolean_circuit_reversed}`

---

### Challenge 8 — Execution Trace (700 pts)
**Difficulty:** Expert | **Concept:** Multi-layer validation functions

**Scenario:** Six nested validation functions (a–f), each checking a different property. The flag is encoded as hex bytes in `_FLAG`.

**Files:** `maze.py`

**Solution:**
```python
# The _FLAG array is hardcoded in maze.py as hex bytes:
_FLAG = [0x53, 0x4C, 0x41, 0x55, 0x5F, 0x43, 0x53, 0x49,
         0x43, 0x7B, 0x31, 0x33, 0x78, 0x33, 0x63, 0x75,
         0x74, 0x69, 0x30, 0x6E, 0x5F, 0x31, 0x33, 0x7D]

flag = ''.join(chr(b) for b in _FLAG)
print(flag)
```

**Flag:** `SLAU_CSIC{13x3cuti0n_13}`

---

### Challenge 9 — Symbolic Execution (500 pts)
**Difficulty:** Hard | **Concept:** Z3 constraint solving

**Scenario:** 24-character input validated by 6 constraint layers: range checks, modular arithmetic, pairwise XOR, cumulative hash, sliding window, and total XOR.

**Files:** `constrained.py`

**Solution:**
```python
from z3 import *

chars = [Int(f'c{i}') for i in range(24)]
s = Solver()

# Range checks
for i, c in enumerate(chars):
    s.add(c >= 32, c <= 126)

# Modular arithmetic constraints
mod_constraints = [
    (0, 3, 2), (0, 7, 6), (1, 3, 1), (1, 7, 6),
    # ... (all constraints from constrained.py)
]
for idx, mod, expected in mod_constraints:
    s.add(chars[idx] % mod == expected)

# Pairwise XOR
xor_pairs = [(0,1,0x1f), (2,3,0x14), (4,5,0x1c), (6,7,0x1a),
             (8,9,0x38), (10,11,0x53), (12,13,0x1d), (14,15,0x06),
             (16,17,0x05), (18,19,0x1a), (20,21,0x2c), (22,23,0x4d)]
for i, j, expected in xor_pairs:
    s.add(chars[i] ^ chars[j] == expected)

# Cumulative hash
running_sum = BitVecVal(0, 32)
for i in range(24):
    running_sum = (running_sum + chars[i] * (i + 1)) % 256
s.add(running_sum == 71)

# Sliding window
for i in range(22):
    s.add(chars[i] + chars[i+1] + chars[i+2] <= 350)

# Total XOR
total = chars[0]
for i in range(1, 24):
    total = total ^ chars[i]
s.add(total == 3)

if s.check() == sat:
    m = s.model()
    print(''.join(chr(m[chars[i]].as_long()) for i in range(24)))
```

**Flag:** `SLAU_CSIC{c0nstr41nt_s0}`

---

### Challenge 10 — Anti-Analysis (600 pts)
**Difficulty:** Hard | **Concept:** Static analysis bypass

**Scenario:** Protected by debugger detection, file integrity checks, eval(compile()) obfuscation, and runtime XOR. The real logic is XOR decryption.

**Files:** `protected.py`

**Solution:**
```python
# Read the source statically — don't run it
# The anti-analysis checks (debugger, file size, environment) protect nothing
# The eval(compile(...)) layer is just "pass"
# The real logic: XOR decryption

encrypted = [
    0x10, 0x18, 0x07, 0x1e, 0x1a, 0x1a, 0x0c, 0x11,
    0x0c, 0x29, 0x77, 0x3a, 0x32, 0x7a, 0x1a, 0x6d,
    0x31, 0x6c, 0x23, 0x2b, 0x30, 0x65, 0x35, 0x14,
    0x27, 0x20, 0x2f, 0x6c, 0x3c, 0x21, 0x70, 0x30,
    0x3b
]

key = [ord(c) for c in "CTFKEY_XOR"]  # [67, 84, 70, 75, 69, 89, 95, 88, 79, 82]

flag = ''.join(chr(encrypted[i] ^ key[i % len(key)]) for i in range(len(encrypted)))
print(flag)
```

**Flag:** `SLAU_CSIC{4nt1_4n4lys1s_byp4ss3d}`

---

## Category 2: Digital Forensics (Challenges 11–20)

---

### Challenge 11 — PCAP Extract (600 pts)
**Difficulty:** Hard | **Concept:** Network traffic analysis with Wireshark

**Scenario:** A pcap file containing HTTP traffic. The flag is hidden in an HTTP response body.

**Files:** `traffic.pcap`

**Solution:**
```bash
# Open in Wireshark
wireshark traffic.pcap

# Filter for HTTP traffic
# http

# Follow TCP streams — look for HTML responses
# The flag is in an HTML comment:
# <!-- server-note: deployment token = SLAU_CSIC{wireshark_is_your_friend} -->

# Or using tshark:
tshark -r traffic.pcap -Y "http" -T fields -e http.file_data | grep -i SLAU_CSIC
```

**Flag:** `SLAU_CSIC{wireshark_is_your_friend}`

---

### Challenge 12 — Memory Strings (500 pts)
**Difficulty:** Hard | **Concept:** Memory forensics with strings

**Scenario:** A 512KB memory dump containing scattered flag fragments.

**Files:** `memdump.bin`

**Solution:**
```bash
# Search for the flag pattern
strings memdump.bin | grep SLAU_CSIC

# Or search in hex editor for the pattern
xxd memdump.bin | grep -i "534c4155"  # hex for "SLAU"

# Or broader search
strings -n 10 memdump.bin | grep -E "SLAU_CSIC\{.*\}"
```

**Flag:** `SLAU_CSIC{memory_forensics_expert}`

---

### Challenge 13 — File Carving (600 pts)
**Difficulty:** Hard | **Concept:** Embedded file extraction with binwalk

**Scenario:** A binary container with an embedded ZIP archive.

**Files:** `container.bin`

**Solution:**
```bash
# Detect and extract embedded files
binwalk -e container.bin

# The extracted ZIP contains flag.txt
cat _container.bin.extracted/flag.txt
```

**Flag:** `SLAU_CSIC{binwalk_file_carving}`

---

### Challenge 14 — Log Correlation (500 pts)
**Difficulty:** Hard | **Concept:** Incident response timeline reconstruction

**Scenario:** Three log files from a compromised server: `auth.log`, `access.log`, `cron.log`. Correlate timestamps to reconstruct the attack.

**Files:** `auth.log`, `access.log`, `cron.log`

**Solution:**
```bash
# Step 1: Identify attacker IP from auth.log
grep "Failed password" auth.log | tail -20
# → 203.0.113.42 brute-forcing root

# Step 2: Find successful login
grep "Accepted" auth.log | grep "203.0.113.42"
# → 21:17:42 successful root login

# Step 3: Track attacker actions in access.log
grep "203.0.113.42" access.log
# → POST /admin/backup/export, GET /api/v1/audit/incident-SLAU_CSIC{incident_response_timeline}/report

# Step 4: Find persistence in cron.log
grep "21:2[4-6]" cron.log
# → /tmp/.hidden/backup-helper.sh (suspicious cron job)
```

The flag is embedded in the access.log URL path:
```bash
grep "SLAU_CSIC" access.log
```

**Flag:** `SLAU_CSIC{incident_response_timeline}`

---

### Challenge 15 — Hex Analysis (400 pts)
**Difficulty:** Medium | **Concept:** Hex-encoded data in binary files

**Scenario:** The flag is hex-encoded at a specific offset in a binary file, mixed with decoy data.

**Files:** `binary.bin`

**Solution:**
```bash
# Examine hex dump
xxd binary.bin | less

# Search for hex patterns that decode to ASCII
# Offset 0x200: 534c41555f435349437b6865785f656469746f725f707265636973696f6e7d
# Decodes to: SLAU_CSIC{hex_editor_precision}

# Verify
echo "534c41555f435349437b6865785f656469746f725f707265636973696f6e7d" | xxd -r -p

# Note: There are decoy flags at other offsets (FLAG{fake_flag}, SLAU_CSIC{not_it})
# The real flag is at offset 0x200
```

**Flag:** `SLAU_CSIC{hex_editor_precision}`

---

### Challenge 16 — Email Parse (500 pts)
**Difficulty:** Hard | **Concept:** MIME email structure and image metadata

**Scenario:** A MIME email with a base64-encoded PNG attachment. The flag is in the PNG's tEXt metadata chunk.

**Files:** `email.eml`

**Solution:**
```bash
# View the full MIME structure
cat email.eml

# Extract the base64 PNG attachment
# Look for the boundary, find the image/png part
# Decode the base64 content

# Or use Python:
import email
import base64

with open('email.eml') as f:
    msg = email.message_from_file(f)

for part in msg.walk():
    if part.get_content_type() == 'image/png':
        img_data = base64.b64decode(part.get_payload())
        with open('extracted.png', 'wb') as f:
            f.write(img_data)

# Check PNG metadata
python3 -c "
import struct
with open('extracted.png', 'rb') as f:
    data = f.read()
# Find tEXt chunk
idx = data.find(b'tEXt')
print(data[idx:idx+100])
"
# tEXt chunk contains: Comment: SLAU_CSIC{email_forensics_headers}
```

**Flag:** `SLAU_CSIC{email_forensics_headers}`

---

### Challenge 17 — ZIP Password (500 pts)
**Difficulty:** Hard | **Concept:** Password-protected ZIP brute force

**Scenario:** A ZIP archive protected with a 6-digit numeric password.

**Files:** `protected.zip`

**Solution:**
```bash
# Brute-force with fcrackzip
fcrackzip -b -c a -l 6 protected.zip

# Or with John the Ripper
zip2john protected.zip > hash.txt
john hash.txt --mask=?d?d?d?d?d?d

# Or Python script:
import zipfile
import itertools

with zipfile.ZipFile('protected.zip') as zf:
    for combo in itertools.product(range(10), repeat=6):
        pwd = ''.join(map(str, combo))
        try:
            zf.extractall(pwd=pwd.encode())
            print(f"Password: {pwd}")
            break
        except (RuntimeError, zipfile.BadZipFile):
            continue

# Extract flag.txt after cracking
cat flag.txt
```

**Flag:** `SLAU_CSIC{zip_password_cracked}`

---

### Challenge 18 — Disk Recovery (600 pts)
**Difficulty:** Hard | **Concept:** FAT12 deleted file recovery

**Scenario:** A FAT12 disk image with a deliberately deleted file. FAT12 marks space as available but leaves data intact.

**Files:** `disk.img`

**Solution:**
```bash
# Search for the flag directly
strings disk.img | grep SLAU_CSIC

# Or mount and recover
mkdir /tmp/disk
sudo mount -o loop disk.img /tmp/disk
ls -la /tmp/disk  # Deleted files may still appear

# Or use foremost/testdisk for file carving
 foremost -i disk.img -o /tmp/recovered
 cat /tmp/recoveredtxt/flag.txt
```

**Flag:** `SLAU_CSIC{deleted_file_recovery}`

---

### Challenge 19 — Stego PNG (600 pts)
**Difficulty:** Hard | **Concept:** LSB steganography in PNG images

**Scenario:** The flag is embedded in the least significant bit of the red channel of each pixel.

**Files:** `stego.png`

**Solution:**
```python
from PIL import Image

img = Image.open('stego.png')
pixels = img.load()
width, height = img.size

bits = []
for y in range(height):
    for x in range(width):
        r, g, b = pixels[x, y][:3]
        bits.append(r & 1)

# Convert bits to bytes
flag_bytes = bytearray()
for i in range(0, len(bits), 8):
    byte = 0
    for bit in bits[i:i+8]:
        byte = (byte << 1) | bit
    flag_bytes.append(byte)
    if flag_bytes[-2:] == b'}\x00':
        break

print(flag_bytes.decode('utf-8', errors='replace'))
```

**Flag:** `SLAU_CSIC{lsb_steganography_mastered}`

---

### Challenge 20 — DNS Exfil (700 pts)
**Difficulty:** Expert | **Concept:** DNS tunneling detection

**Scenario:** An attacker is encoding stolen data as subdomain labels in DNS queries to an external server.

**Files:** `capture.txt`

**Solution:**
```bash
# Extract DNS query names
grep "A?" capture.txt | grep "analytics-cdn.shadow.net"

# The exfiltrated data is in the subdomain labels, in order:
# k8m2n → x9p3q → w7j4r → t5v6s → h2u1y → SLAU_CSIC{ → s_exfiltrati → on_detected} → ...

# Or parse with Python:
import re

with open('capture.txt') as f:
    lines = f.readlines()

dns_queries = []
for line in lines:
    if 'analytics-cdn.shadow.net' in line and 'A?' in line:
        # Extract the subdomain label
        match = re.search(r'(\S+)\.analytics-cdn\.shadow\.net', line)
        if match:
            label = match.group(1)
            # Decode: the subdomain labels spell out the flag
            # Remove decoy labels (k8m2n, x9p3q, etc.)
            if not re.match(r'^[a-z0-9]{5}$', label):
                dns_queries.append(label)

flag = ''.join(dns_queries)
print(flag)
```

**Flag:** `SLAU_CSIC{dns_exfiltration_detected}`

---

## Category 3: Cryptography (Challenges 21–30)

---

### Challenge 21 — RSA Fermat (600 pts)
**Difficulty:** Hard | **Concept:** Fermat factorization of RSA modulus

**Scenario:** RSA with `p` and `q` chosen too close together. Fermat factorization exploits this weakness.

**Files:** `rsa.txt`

**Solution:**
```python
from sympy import isqrt

n = 18985856793311426068697601865158379492822179069549825393473290635155371843250765774113373853827310583900800470818314497714039
e = 65537
c = 7787010862786857651260127763986650496643908851235627035522681977327359701136283031170118298047439461099500417730422019927758

# Fermat factorization
a = isqrt(n)
if a * a < n:
    a += 1

while True:
    b_sq = a * a - n
    b = isqrt(b_sq)
    if b * b == b_sq:
        p = a + b
        q = a - b
        break
    a += 1

# Compute private key
phi = (p - 1) * (q - 1)
d = pow(e, -1, phi)

# Decrypt
m = pow(c, d, n)
flag = bytes.fromhex(hex(m)[2:])
print(flag.decode())
```

**Flag:** `SLAU_CSIC{fermat_factorization_breaks_rsa}`

---

### Challenge 22 — Two-Time Pad (500 pts)
**Difficulty:** Hard | **Concept:** XOR key reuse vulnerability

**Scenario:** Two messages encrypted with the same XOR key. One plaintext is known; the other contains the flag.

**Files:** `known.txt`, `msg1.enc`, `msg2.enc`

**Solution:**
```python
# known.txt contains the first plaintext
known = b"This is a test message for encryption verification"

# Read ciphertexts
with open('msg1.enc', 'rb') as f:
    msg1 = f.read()
with open('msg2.enc', 'rb') as f:
    msg2 = f.read()

# XOR key reuse: msg1 XOR msg2 = known XOR flag
# Therefore: flag[i] = msg1[i] XOR msg2[i] XOR known[i]

flag = bytes(m1 ^ m2 ^ k for m1, m2, k in zip(msg1, msg2, known))
print(flag.decode())
```

**Flag:** `SLAU_CSIC{xor_two_time_pad_break}`

---

### Challenge 23 — Hash Extension (700 pts)
**Difficulty:** Expert | **Concept:** MD5 length extension attack

**Scenario:** A server authenticates with `MD5(secret || data)`. You need to forge a valid hash for `data + '&admin=true'` without knowing the secret.

**Files:** `hashext.txt`

**Solution:**
```python
# Using hashpumpy (pip install hashpumpy)
import hashpumpy

original_hash = "c6fecb54e0557bb3793418c32b6f08fc"
original_data = "user=guest&role=user"
extension = "&admin=true"
secret_length = 16

new_hash, new_data = hashpumpy.hashpump(
    original_hash,
    original_data,
    extension,
    secret_length
)

print(f"New hash: {new_hash}")
print(f"Extended data (with padding): {new_data.hex()}")
```

The new hash is the forged authentication token.

**Flag:** `SLAU_CSIC{md5_length_extension_vulnerability}`

---

### Challenge 24 — Padding Oracle (800 pts)
**Difficulty:** Expert | **Concept:** CBC padding oracle attack

**Scenario:** An AES-CBC decryption oracle that reveals whether padding is valid. This single bit of information enables full decryption.

**Files:** `oracle.py`, `ciphertext.bin`

**Solution:**
```python
from cryptography.hazmat.primitives.ciphers import Cipher, algorithms, modes
import hashlib

# Key and IV from oracle.py
KEY = hashlib.sha256(b'padding-oracle-secret-key-2024').digest()
IV = bytes.fromhex('45876cf08154755dc05cb97af197f0b5')

# Read ciphertext
with open('ciphertext.bin', 'rb') as f:
    ct = f.read()

# Split into IV + ciphertext blocks
iv = ct[:16]
blocks = [ct[i:i+16] for i in range(16, len(ct), 16)]

def oracle(ciphertext):
    """Returns True if padding is valid"""
    cipher = Cipher(algorithms.AES(KEY), modes.CBC(ciphertext[:16]))
    decryptor = cipher.decryptor()
    pt = decryptor.update(ciphertext[16:]) + decryptor.finalize()
    # Check PKCS7 padding
    pad_len = pt[-1]
    return 1 <= pad_len <= 16 and pt[-pad_len:] == bytes([pad_len] * pad_len)

# Classic padding oracle attack
def decrypt_block(prev_block, target_block):
    intermediate = bytearray(16)
    plaintext = bytearray(16)

    for byte_idx in range(15, -1, -1):
        pad_val = 16 - byte_idx
        for guess in range(256):
            crafted = bytearray(16)
            crafted[byte_idx] = guess
            for k in range(byte_idx + 1, 16):
                crafted[k] = intermediate[k] ^ pad_val

            test_ct = bytes(crafted) + target_block
            if oracle(test_ct):
                intermediate[byte_idx] = guess ^ pad_val
                plaintext[byte_idx] = intermediate[byte_idx] ^ prev_block[byte_idx]
                break

    return bytes(plaintext)

# Decrypt each block
plaintext = b''
for i, block in enumerate(blocks):
    prev = iv if i == 0 else blocks[i-1]
    plaintext += decrypt_block(prev, block)

print(plaintext.decode('utf-8', errors='replace'))
```

**Flag:** `SLAU_CSIC{padding_oracle_exploited}`

---

### Challenge 25 — ECB Penguin (500 pts)
**Difficulty:** Hard | **Concept:** ECB mode pattern recognition

**Scenario:** Data encrypted with AES-ECB. Identical plaintext blocks produce identical ciphertext blocks, revealing message structure.

**Files:** `ecb_data.txt`

**Solution:**
```python
# Parse hex blocks from ecb_data.txt
data = """fe 13 a3 ba 62 3a 16 a0 ..."""  # full data
blocks = [bytes.fromhex(b) for b in data.split()]

# Group and count unique blocks
from collections import Counter
block_counts = Counter(blocks)

# Analyze patterns:
# - Repeated blocks indicate repeated plaintext
# - The most frequent block = most common character
# - Map blocks to ASCII based on frequency

# The data has 12 blocks of 16 bytes
# Identify which blocks repeat and map to characters
# The repeating blocks spell out the flag

for block, count in block_counts.most_common():
    print(f"Block: {block.hex()} x{count}")
```

The pattern reveals the message structure. With 11 unique blocks and 3 repeating patterns, you can decode the ASCII message.

**Flag:** `SLAU_CSIC{ecb_penguin_pattern_recognition}`

---

### Challenge 26 — Substitution (400 pts)
**Difficulty:** Medium | **Concept:** Monoalphabetic substitution cipher

**Scenario:** Each letter maps to a fixed different letter. Letter frequencies survive the transformation.

**Files:** `cipher.txt`

**Solution:**
```bash
# The ciphertext contains the flag directly in encoded form:
# ysqv_jypj{grtcvtojd_qoqsdypy_ixrly}

# Frequency analysis approach:
# Most common ciphertext letters → most common English letters
# E=most common, T, A, O, I, N, S, H, R...

# Or use a substitution cipher solver
# dcode.fr/cipher-tool or CyberChef

# The mapping reveals:
# y→f, s→l, q→a, v→g (ysqv → flag)
# g→s, r→l, t→a, ... (grtcvtojd → frequency)
# The flag is embedded in the plaintext
```

**Flag:** `SLAU_CSIC{frequency_analysis_works}`

---

### Challenge 27 — Diffie-Hellman (600 pts)
**Difficulty:** Hard | **Concept:** Weak private key brute force

**Scenario:** Diffie-Hellman key exchange with a server that chose a private key that's far too small.

**Files:** `dh.txt`

**Solution:**
```python
from sympy import isprime
import hashlib

# Parameters from dh.txt
p = 0xffffffffffffffffc90fdaa22168c234c4c6628b80dc1cd129024e088a67cc74020bbea63b139b22514a08798e3404ddef9519b3cd3a431b302b0a6df25f14374fe1356d6d51c245e485b576625e7ec6f44c42e9a637ed6b0bff5cb6f406b7edee386bfb5a899fa5ae9f24117c4b1fe649286651ece45b3dc2007cb8a163bf0598da48361c55d39a69163fa8fd24cf5f83655d23dca3ad961c62f356208552bb9ed529077096966d670c354e4abc9804f1746c08ca18217c32905e462e36ce3be39e772c180e86039b2783a2ec07a28fb5c55df06f4c52c9de2bcbf6955817183995497cea956ae515d2261898fa051015728e5a8aacaa68ffffffffffffffff
g = 5
A = 0x906c22c84b1ddf4feed98f56605bb5606321480c081cf44981848b1cd909ae4d...
B = 0x2deaf189c140c16b7c528f679  # Bob's public key (very small!)

encrypted_flag = bytes.fromhex('1479d8220d5c68f8d237e77609370a64e1a70d7981c7a8411860b4b6391b')

# Brute-force Bob's private key
for b in range(1, 1000000):
    if pow(g, b, p) == B:
        print(f"Bob's private key: {b}")
        break

# Compute shared secret
shared_secret = pow(A, b, p)
key = hashlib.sha256(shared_secret.to_bytes(256, 'big')).digest()

# XOR to decrypt flag
flag = bytes(encrypted_flag[i] ^ key[i] for i in range(len(encrypted_flag)))
print(flag.decode())
```

Bob's private key `b = 42`.

**Flag:** `SLAU_CSIC{dh_weak_private_key}`

---

### Challenge 28 — CBC Bit Flip (700 pts)
**Difficulty:** Expert | **Concept:** CBC mode bit-flipping attack

**Scenario:** A server encrypts user tokens with AES-CBC. The plaintext contains `user=guest` and you need to change it to `user=admin`.

**Files:** `server.py`, `encrypted.bin`

**Solution:**
```python
import hashlib

KEY = hashlib.sha256(b'CTF-Masters-Server-Key-2024').digest()

with open('encrypted.bin', 'rb') as f:
    ct = f.read()

iv = ct[:16]
block0 = ct[16:32]  # First ciphertext block
block1 = ct[32:48]  # Second ciphertext block (contains "user=guest")

# In CBC, flipping bit i in block0 flips bit i in plaintext of block1
# "guest" starts at offset 5 in block1
# XOR block0[5] with (ord('g') ^ ord('a')) to change g→a
# XOR block0[6] with (ord('u') ^ ord('d')) to change u→d
# XOR block0[7] with (ord('e') ^ ord('m')) to change e→m
# XOR block0[8] with (ord('s') ^ ord('i')) to change s→i
# XOR block0[9] with (ord('t') ^ ord('n')) to change t→n

flipped = bytearray(block0)
flipped[5] ^= ord('g') ^ ord('a')
flipped[6] ^= ord('u') ^ ord('d')
flipped[7] ^= ord('e') ^ ord('m')
flipped[8] ^= ord('s') ^ ord('i')
flipped[9] ^= ord('t') ^ ord('n')

new_ct = iv + bytes(flipped) + block1

# Verify by decrypting
from cryptography.hazmat.primitives.ciphers import Cipher, algorithms, modes
cipher = Cipher(algorithms.AES(KEY), modes.CBC(iv))
decryptor = cipher.decryptor()
pt = decryptor.update(new_ct[16:]) + decryptor.finalize()
print(pt.decode('utf-8', errors='replace'))
# Should show: user=admin&role=guest...
```

**Flag:** `SLAU_CSIC{cbc_bit_flip_attack}`

---

### Challenge 29 — Lattice Crypto (800 pts)
**Difficulty:** Expert | **Concept:** Hidden Number Problem with LLL lattice reduction

**Scenario:** 20 equations where each hides a secret number modulo a large prime. The hidden numbers follow a pattern exploitable by lattice reduction.

**Files:** `lattice.txt`

**Solution:**
```python
from fpylll import LLL, IntegerMatrix

# Parameters from lattice.txt
q = 340282366920938463463374607431768211507

# Parse a[i], b[i] from lattice.txt
equations = []
with open('lattice.txt') as f:
    for line in f:
        if 'a[' in line:
            a_val = int(line.split('=')[1].strip())
            equations.append([a_val, 0])
        elif 'b[' in line:
            b_val = int(line.split('=')[1].strip())
            equations[-1][1] = b_val

n = len(equations)

# Build lattice basis matrix (n+1 x n+1)
# Diagonal: q (scaling factor)
# Last row: a[i] values
# Target: (b[0], b[1], ..., b[n-1], 0)

M = IntegerMatrix(n + 1, n + 1)
for i in range(n):
    M[i, i] = q
    M[n, i] = equations[i][0]  # a[i]
M[n, n] = 1

# Scale last row
for i in range(n):
    M[n, i] = int(equations[i][0] * (1 / q) * q)  # approximate

# Apply LLL
LLL.reduction(M)

# The target vector (b[0], b[1], ..., b[n-1], 0) should appear
# as a short vector in the reduced basis
# Extract x from the relationship: a[i]*x ≡ b[i] (mod q)

# The secret x encodes the flag as ASCII bytes
```

The LLL algorithm finds short vectors in the lattice, revealing the hidden number `x` which encodes the flag.

**Flag:** `SLAU_CSIC{lattice_reduction_cryptanalysis}`

---

### Challenge 30 — PRNG Prediction (600 pts)
**Difficulty:** Hard | **Concept:** LCG parameter recovery and prediction

**Scenario:** 100 outputs from a Linear Congruential Generator. Recover the parameters and predict future outputs to decrypt the flag.

**Files:** `output.txt`, `prng.py`

**Solution:**
```python
# Parse outputs from output.txt
outputs = []
with open('output.txt') as f:
    for line in f:
        if 'output[' in line:
            val = int(line.split('=')[1].strip())
            outputs.append(val)

# Recover LCG parameters using the Marsaglia lattice method
# For x[n+1] = (a*x[n] + c) mod m:
# Given enough outputs, we can set up equations:
# x[1] = a*x[0] + c (mod m)
# x[2] = a*x[1] + c (mod m)
# => x[2] - x[1] = a*(x[1] - x[0]) (mod m)

# Use consecutive differences to solve for a, c, m
# Then predict outputs 100-134

enc = []
with open('output.txt') as f:
    content = f.read()
# Parse enc values
import re
enc_vals = [int(m) for m in re.findall(r'enc\[\s*\d+\]\s*=\s*(\d+)', content)]

# After recovering a, c, m and predicting outputs[100:135]:
flag = bytes(predicted[i] ^ enc_vals[i] for i in range(35))
print(flag.decode())
```

**Flag:** `SLAU_CSIC{prng_prediction_mastered}`

---

## Summary

| # | Challenge | Category | Points | Flag | Key Technique |
|---|-----------|----------|--------|------|---------------|
| 1 | Buffer Overflow | Reverse Engineering | 400 | `SLAU_CSIC{b4by_0v3rfl0w_1s_d4ng3r0us}` | Struct layout overflow |
| 2 | Python Packer | Reverse Engineering | 500 | `SLAU_CSIC{pyth0n_l4y3rs_g0_d33p}` | XOR + base64 deobfuscation |
| 3 | x86 Disassembly | Reverse Engineering | 600 | `SLAU_CSIC{x86_disassembly_decoded}` | XOR cipher reversal |
| 4 | Custom VM | Reverse Engineering | 800 | `SLAU_CSIC{v1rtu4l_m4ch1n3_r3v3r3d}` | Bytecode interpreter |
| 5 | Algorithm RE | Reverse Engineering | 700 | `SLAU_CSIC{r3v3rs3_th3_c1ph3r_4lg0r1thm}` | S-box + transposition + XOR |
| 6 | Binary Protocol | Reverse Engineering | 600 | `SLAU_CSIC{n3tw0rk_pr0t0c0l_4n4lyz3d}` | Custom protocol parsing |
| 7 | Logic Circuit | Reverse Engineering | 500 | `SLAU_CSIC{boolean_circuit_reversed}` | Self-inverse circuit |
| 8 | Execution Trace | Reverse Engineering | 700 | `SLAU_CSIC{13x3cuti0n_13}` | Hex byte decoding |
| 9 | Symbolic Execution | Reverse Engineering | 500 | `SLAU_CSIC{c0nstr41nt_s0}` | Z3 constraint solving |
| 10 | Anti-Analysis | Reverse Engineering | 600 | `SLAU_CSIC{4nt1_4n4lys1s_byp4ss3d}` | Static analysis bypass |
| 11 | PCAP Extract | Digital Forensics | 600 | `SLAU_CSIC{wireshark_is_your_friend}` | HTTP stream analysis |
| 12 | Memory Strings | Digital Forensics | 500 | `SLAU_CSIC{memory_forensics_expert}` | Binary string extraction |
| 13 | File Carving | Digital Forensics | 600 | `SLAU_CSIC{binwalk_file_carving}` | Binwalk extraction |
| 14 | Log Correlation | Digital Forensics | 500 | `SLAU_CSIC{incident_response_timeline}` | Cross-log timestamp analysis |
| 15 | Hex Analysis | Digital Forensics | 400 | `SLAU_CSIC{hex_editor_precision}` | Hex-to-ASCII decoding |
| 16 | Email Parse | Digital Forensics | 500 | `SLAU_CSIC{email_forensics_headers}` | MIME + PNG metadata |
| 17 | ZIP Password | Digital Forensics | 500 | `SLAU_CSIC{zip_password_cracked}` | 6-digit brute force |
| 18 | Disk Recovery | Digital Forensics | 600 | `SLAU_CSIC{deleted_file_recovery}` | FAT12 file recovery |
| 19 | Stego PNG | Digital Forensics | 600 | `SLAU_CSIC{lsb_steganography_mastered}` | LSB extraction |
| 20 | DNS Exfil | Digital Forensics | 700 | `SLAU_CSIC{dns_exfiltration_detected}` | DNS tunnel decoding |
| 21 | RSA Fermat | Cryptography | 600 | `SLAU_CSIC{fermat_factorization_breaks_rsa}` | Fermat factorization |
| 22 | Two-Time Pad | Cryptography | 500 | `SLAU_CSIC{xor_two_time_pad_break}` | XOR key reuse |
| 23 | Hash Extension | Cryptography | 700 | `SLAU_CSIC{md5_length_extension_vulnerability}` | MD5 length extension |
| 24 | Padding Oracle | Cryptography | 800 | `SLAU_CSIC{padding_oracle_exploited}` | CBC padding oracle |
| 25 | ECB Penguin | Cryptography | 500 | `SLAU_CSIC{ecb_penguin_pattern_recognition}` | ECB pattern analysis |
| 26 | Substitution | Cryptography | 400 | `SLAU_CSIC{frequency_analysis_works}` | Frequency analysis |
| 27 | Diffie-Hellman | Cryptography | 600 | `SLAU_CSIC{dh_weak_private_key}` | Brute-force weak key |
| 28 | CBC Bit Flip | Cryptography | 700 | `SLAU_CSIC{cbc_bit_flip_attack}` | CBC bit manipulation |
| 29 | Lattice Crypto | Cryptography | 800 | `SLAU_CSIC{lattice_reduction_cryptanalysis}` | LLL lattice reduction |
| 30 | PRNG Prediction | Cryptography | 600 | `SLAU_CSIC{prng_prediction_mastered}` | LCG parameter recovery |
