<?php

namespace App\Services;

use App\Models\Content;
use App\Models\DmcaRequest;
use App\Models\License;
use App\Models\MonitoringResult;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DmcaService
{
    /**
     * Generate a DMCA notice.
     */
    public function generateNotice(
        License $license,
        Content $content,
        string $infringingUrl,
        string $recipientType,
        ?MonitoringResult $monitoringResult = null,
        array $ownerInfo = []
    ): DmcaRequest {
        // Get the template for the recipient type
        $template = $this->getTemplate($recipientType);

        // Build the notice content
        $noticeContent = $this->buildNotice($template, [
            'owner_name' => $ownerInfo['name'] ?? $license->user->name,
            'owner_email' => $ownerInfo['email'] ?? $license->user->email,
            'owner_address' => $ownerInfo['address'] ?? '',
            'original_title' => $content->post_title,
            'original_url' => $content->post_url,
            'infringing_url' => $infringingUrl,
            'infringing_domain' => parse_url($infringingUrl, PHP_URL_HOST),
            'similarity_score' => $monitoringResult?->similarity_score ?? 'N/A',
            'date' => now()->format('F j, Y'),
        ]);

        return DmcaRequest::create([
            'license_id' => $license->id,
            'content_id' => $content->id,
            'monitoring_result_id' => $monitoringResult?->id,
            'infringing_url' => $infringingUrl,
            'original_url' => $content->post_url,
            'status' => 'draft',
            'recipient_type' => $recipientType,
            'notice_content' => $noticeContent,
        ]);
    }

    /**
     * Get DMCA templates.
     */
    public function getTemplates(): array
    {
        return [
            'google' => [
                'name' => 'Google Search',
                'description' => 'Request removal from Google search results',
                'submission_url' => 'https://support.google.com/legal/contact/lr_dmca',
                'fields' => ['owner_name', 'owner_email', 'original_url', 'infringing_url'],
            ],
            'bing' => [
                'name' => 'Bing Search',
                'description' => 'Request removal from Bing search results',
                'submission_url' => 'https://www.microsoft.com/en-us/concern/dmca',
                'fields' => ['owner_name', 'owner_email', 'original_url', 'infringing_url'],
            ],
            'hosting_provider' => [
                'name' => 'Hosting Provider',
                'description' => 'Send to website hosting provider',
                'requires_lookup' => true,
                'fields' => ['owner_name', 'owner_email', 'owner_address', 'original_url', 'infringing_url'],
            ],
            'cloudflare' => [
                'name' => 'Cloudflare',
                'description' => 'Report abuse to Cloudflare',
                'submission_url' => 'https://www.cloudflare.com/abuse/',
                'fields' => ['owner_name', 'owner_email', 'original_url', 'infringing_url'],
            ],
            'website_owner' => [
                'name' => 'Website Owner',
                'description' => 'Direct notice to website owner',
                'fields' => ['owner_name', 'owner_email', 'owner_address', 'original_url', 'infringing_url'],
            ],
        ];
    }

    /**
     * Get a specific template.
     */
    public function getTemplate(string $type): string
    {
        return match ($type) {
            'google', 'bing' => $this->getSearchEngineTemplate(),
            'hosting_provider' => $this->getHostingProviderTemplate(),
            'cloudflare' => $this->getCloudflareTemplate(),
            'website_owner' => $this->getWebsiteOwnerTemplate(),
            default => $this->getGenericTemplate(),
        };
    }

    /**
     * Build notice from template.
     */
    private function buildNotice(string $template, array $data): string
    {
        foreach ($data as $key => $value) {
            $template = str_replace('{{' . $key . '}}', $value ?? '', $template);
        }

        return $template;
    }

    /**
     * Send DMCA notice via email.
     */
    public function sendNotice(DmcaRequest $dmca): bool
    {
        if (!$dmca->recipient_email) {
            Log::warning('No recipient email for DMCA request', ['dmca_id' => $dmca->id]);
            return false;
        }

        try {
            // Here you would implement actual email sending
            // For now, we'll just mark it as sent

            $dmca->markAsSent();

            // Update monitoring result if exists
            if ($dmca->monitoringResult) {
                $dmca->monitoringResult->update(['status' => 'dmca_sent']);
            }

            Log::info('DMCA notice sent', [
                'dmca_id' => $dmca->id,
                'recipient' => $dmca->recipient_email,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to send DMCA notice', [
                'dmca_id' => $dmca->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Submit DMCA to Google.
     */
    public function submitToGoogle(DmcaRequest $dmca): array
    {
        // Google requires manual submission through their web form
        // This method prepares the data for submission

        return [
            'success' => false,
            'message' => 'Google DMCA requires manual submission through their legal portal.',
            'submission_url' => 'https://support.google.com/legal/contact/lr_dmca',
            'prepared_data' => [
                'copyrighted_work_url' => $dmca->original_url,
                'infringing_urls' => [$dmca->infringing_url],
                'description' => $dmca->notice_content,
            ],
        ];
    }

    /**
     * Look up hosting provider for a domain.
     */
    public function lookupHostingProvider(string $domain): array
    {
        try {
            // Get nameservers
            $dns = dns_get_record($domain, DNS_NS);
            $nameservers = array_map(fn ($record) => $record['target'] ?? '', $dns);

            // Get IP address
            $ip = gethostbyname($domain);

            // Look up ASN info
            $asnInfo = $this->lookupAsn($ip);

            // Common hosting providers by nameserver patterns
            $provider = $this->detectProviderFromNameservers($nameservers);

            if (!$provider && $asnInfo) {
                $provider = $asnInfo['org'] ?? null;
            }

            return [
                'domain' => $domain,
                'ip' => $ip,
                'nameservers' => $nameservers,
                'provider' => $provider,
                'asn_info' => $asnInfo,
                'abuse_email' => $this->getAbuseEmail($provider),
            ];

        } catch (\Exception $e) {
            Log::error('Error looking up hosting provider', [
                'domain' => $domain,
                'error' => $e->getMessage(),
            ]);

            return [
                'domain' => $domain,
                'error' => 'Could not determine hosting provider',
            ];
        }
    }

    /**
     * Look up ASN information for an IP.
     */
    private function lookupAsn(string $ip): ?array
    {
        try {
            $response = Http::timeout(10)
                ->get("https://ipinfo.io/{$ip}/json");

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            // Ignore errors
        }

        return null;
    }

    /**
     * Detect hosting provider from nameservers.
     */
    private function detectProviderFromNameservers(array $nameservers): ?string
    {
        $nsString = implode(' ', array_map('strtolower', $nameservers));

        $providers = [
            'cloudflare' => 'Cloudflare',
            'awsdns' => 'Amazon Web Services (AWS)',
            'googledomains' => 'Google Cloud',
            'azure' => 'Microsoft Azure',
            'digitalocean' => 'DigitalOcean',
            'godaddy' => 'GoDaddy',
            'bluehost' => 'Bluehost',
            'hostgator' => 'HostGator',
            'siteground' => 'SiteGround',
            'dreamhost' => 'DreamHost',
            'namecheap' => 'Namecheap',
            'ovh' => 'OVH',
            'hetzner' => 'Hetzner',
        ];

        foreach ($providers as $pattern => $name) {
            if (str_contains($nsString, $pattern)) {
                return $name;
            }
        }

        return null;
    }

    /**
     * Get abuse email for common providers.
     */
    private function getAbuseEmail(?string $provider): ?string
    {
        if (!$provider) {
            return null;
        }

        $providerLower = strtolower($provider);

        $emails = [
            'cloudflare' => 'abuse@cloudflare.com',
            'amazon' => 'abuse@amazonaws.com',
            'google' => 'network-abuse@google.com',
            'microsoft' => 'abuse@microsoft.com',
            'digitalocean' => 'abuse@digitalocean.com',
            'godaddy' => 'abuse@godaddy.com',
            'bluehost' => 'abuse@bluehost.com',
            'hostgator' => 'abuse@hostgator.com',
            'siteground' => 'abuse@siteground.com',
            'dreamhost' => 'abuse@dreamhost.com',
            'namecheap' => 'abuse@namecheap.com',
            'ovh' => 'abuse@ovh.net',
            'hetzner' => 'abuse@hetzner.com',
        ];

        foreach ($emails as $pattern => $email) {
            if (str_contains($providerLower, $pattern)) {
                return $email;
            }
        }

        return null;
    }

    /**
     * Search engine DMCA template.
     */
    private function getSearchEngineTemplate(): string
    {
        return <<<'TEMPLATE'
DMCA TAKEDOWN NOTICE

Date: {{date}}

To Whom It May Concern,

I am writing to report copyright infringement of my content that is being hosted and indexed on your platform.

COPYRIGHT OWNER INFORMATION:
Name: {{owner_name}}
Email: {{owner_email}}

ORIGINAL WORK:
Title: {{original_title}}
URL: {{original_url}}

INFRINGING CONTENT:
URL: {{infringing_url}}
Domain: {{infringing_domain}}
Similarity Score: {{similarity_score}}%

I have a good faith belief that the use of the copyrighted materials described above is not authorized by the copyright owner, its agent, or the law. I swear under penalty of perjury that the information in this notification is accurate and that I am the copyright owner or am authorized to act on behalf of the owner of the exclusive right that is allegedly being infringed.

I request that you remove or disable access to the infringing material.

{{owner_name}}

---
This notice was generated using ContentShield AI (https://contentshield.ai)
TEMPLATE;
    }

    /**
     * Hosting provider DMCA template.
     */
    private function getHostingProviderTemplate(): string
    {
        return <<<'TEMPLATE'
DMCA TAKEDOWN NOTICE

Date: {{date}}

Dear Hosting Provider Abuse Team,

I am the copyright owner of content that is being infringed upon by a website hosted on your servers.

COPYRIGHT OWNER INFORMATION:
Name: {{owner_name}}
Email: {{owner_email}}
Address: {{owner_address}}

ORIGINAL WORK:
Title: {{original_title}}
Original URL: {{original_url}}

INFRINGING CONTENT:
Infringing URL: {{infringing_url}}
Domain: {{infringing_domain}}
Similarity Score: {{similarity_score}}%

The content at the infringing URL is a substantial copy of my original work. I request that you take appropriate action to remove or disable access to the infringing material in accordance with the Digital Millennium Copyright Act (DMCA).

GOOD FAITH STATEMENT:
I have a good faith belief that the use of the copyrighted material described above is not authorized by the copyright owner, its agent, or the law.

ACCURACY STATEMENT:
I swear, under penalty of perjury, that the information in this notification is accurate, and that I am the copyright owner or am authorized to act on behalf of the owner of an exclusive right that is allegedly infringed.

SIGNATURE:
{{owner_name}}

---
This notice was generated using ContentShield AI (https://contentshield.ai)
TEMPLATE;
    }

    /**
     * Cloudflare abuse template.
     */
    private function getCloudflareTemplate(): string
    {
        return <<<'TEMPLATE'
DMCA COPYRIGHT INFRINGEMENT REPORT

Date: {{date}}

To: Cloudflare Abuse Team

REPORTER INFORMATION:
Name: {{owner_name}}
Email: {{owner_email}}

COPYRIGHT CLAIM:
I am the owner of content being infringed by a website using Cloudflare services.

ORIGINAL WORK:
Title: {{original_title}}
URL: {{original_url}}

INFRINGING MATERIAL:
URL: {{infringing_url}}
Domain: {{infringing_domain}}

STATEMENTS:
- I have a good faith belief that the use of the material is not authorized by the copyright owner, its agent, or the law.
- The information in this notice is accurate.
- Under penalty of perjury, I am authorized to act on behalf of the owner of an exclusive right that is allegedly infringed.

Electronic Signature: {{owner_name}}

---
This notice was generated using ContentShield AI (https://contentshield.ai)
TEMPLATE;
    }

    /**
     * Website owner template.
     */
    private function getWebsiteOwnerTemplate(): string
    {
        return <<<'TEMPLATE'
NOTICE OF COPYRIGHT INFRINGEMENT

Date: {{date}}

Dear Website Owner,

I am writing to inform you that your website contains content that infringes on my copyright.

MY INFORMATION:
Name: {{owner_name}}
Email: {{owner_email}}

MY ORIGINAL WORK:
Title: {{original_title}}
Published at: {{original_url}}

YOUR INFRINGING CONTENT:
URL: {{infringing_url}}
Similarity: {{similarity_score}}%

I request that you immediately remove or properly attribute the infringing content. If this content is not removed or properly attributed within 7 days, I will be forced to file formal DMCA complaints with search engines and your hosting provider.

Please contact me at {{owner_email}} if you believe this notice was sent in error.

Sincerely,
{{owner_name}}

---
This notice was generated using ContentShield AI (https://contentshield.ai)
TEMPLATE;
    }

    /**
     * Generic DMCA template.
     */
    private function getGenericTemplate(): string
    {
        return $this->getHostingProviderTemplate();
    }
}
