# Needs Validation: Deployment and Environment Blockers

This document records candidate findings and security hypotheses that cannot be definitively resolved from repository source code alone and require owner observation or live configuration verification.

---

## 1. CUCIIN-INFRA-EC2-DIRECT-PORT-EXPOSURE

- **Title**: Direct Public Access to EC2 Ports 80 and 443 Bypassing Cloudflare Edge Security Controls
- **Verdict**: needs_validation
- **Affected Boundary**: External Internet -> Origin AWS EC2 Web Server (`3.25.167.8`)
- **Claimed Root Cause**: System operations documentation (`docs/sistem.md:12`) states:
  ```text
  Security Group Inbound: Port 22 (SSH), Port 80 (HTTP), Port 443 (HTTPS) terbuka untuk 0.0.0.0/0.
  ```
  While the domain DNS (`portal-cuciin.web.id`) routes through Cloudflare, opening ports 80 and 443 to `0.0.0.0/0` on the AWS EC2 instance allows attackers to bypass Cloudflare WAF, DDoS mitigation, and edge rate limiters by directing traffic directly to `http://3.25.167.8/`.

### Source Trace and Evidence
- **Entrypoint**: `docs/sistem.md:12`
  ```text
  Security Group Inbound: Port 22 (SSH), Port 80 (HTTP), Port 443 (HTTPS) terbuka untuk 0.0.0.0/0.
  ```
- **Sink**: `bootstrap/app.php:19`
  ```php
  $middleware->trustProxies(at: '*');
  ```

### Decisive Blocker
AWS EC2 Security Group rules and active iptables/Nginx origin restrictions cannot be verified directly from git repository files. The documentation records `0.0.0.0/0`, but out-of-band firewall rules or Cloudflare Authenticated Origin Pulls (AOP) may have been applied on the live server.

### Safe Validation Plan
1. **Local Configuration Check**:
   - Check the Nginx virtual host file (`/etc/nginx/sites-available/cuci-in`) mentioned in `docs/sistem.md`.
   - Verify whether `allow` directives for Cloudflare IP ranges and `deny all;` exist in the server configuration block.
2. **Owner Deployment Verification**:
   - The infrastructure owner should open the AWS Management Console: `EC2 > Security Groups > cuci-in security group`.
   - Inspect Inbound Rules for Port 80 and Port 443.
   - Confirm whether Source is set to `0.0.0.0/0`. If set to `0.0.0.0/0`, restrict inbound traffic strictly to Cloudflare's published IP prefixes:
     `https://www.cloudflare.com/ips-v4` and `https://www.cloudflare.com/ips-v6`.

---

## 2. CUCIIN-COMPLAINT-PHOTO-PUBLIC-EXPOSURE

- **Title**: Customer Complaint Photos Stored on Public Web Root Symlink
- **Verdict**: needs_validation
- **Affected Boundary**: External Internet -> Customer Uploaded Complaint Imagery
- **Claimed Root Cause**: In `CustomerComplaintController::store`, complaint images are stored via:
  ```php
  $photoPath = $request->file('photo')->store('complaints', 'public');
  ```
  The `public` disk maps to `storage/app/public/complaints/`, which is symlinked to `public/storage/complaints/`.
  The accessor in `app/Models/Complaint.php` generates public URLs:
  ```php
  return asset('storage/'.$this->photo_path);
  ```
  Unlike payment proofs which are stored on the `local` private disk and served through authenticated streaming controllers, complaint images are statically served by Nginx without authentication.

### Decisive Blocker
Laravel generates high-entropy random hashes (40 hexadecimal characters) for uploaded filenames (`Str::random(40)`). Whether this constitutes an unacceptable privacy violation depends on organizational data classification:
- If customer garment photographs are considered public/semi-public support attachments, high-entropy unguessable URLs may be acceptable.
- If garment photographs contain sensitive personal effects or identifiable personal property, they must be moved to the private disk with policy-controlled streaming.

### Safe Validation Plan
1. **Stakeholder Policy Review**:
   - Determine whether customer garment condition photos are classified as sensitive PII.
   - If classified as sensitive, migrate storage to `local` disk and introduce `CustomerComplaintController::photo` guarded by `ComplaintPolicy`.

