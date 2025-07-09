<?php

namespace Plugin\Akismet;

class Helper extends \Prefab
{
    /**
     * Check if a string is spam
     *
     * @param  string $type    One of: issue, comment
     */
    public function isSpam(string $string, string $type = "issue", ?string $ip = null, ?string $agent = null): bool
    {
        $f3 = \Base::instance();

        // Get API key
        $key = $f3->get("site.plugins.akismet.api_key");
        if (!$key) {
            return false;
        }

        // Get user data
        if ($ip === null) {
            $ip = $f3->get("IP");
        }

        if ($agent === null) {
            $agent = $f3->get("AGENT");
        }

        // Build request object
        $url = $f3->get("site.url");
        $params = [
            "blog" => $url,
            "comment_content" => $string,
            "comment_type" => $type,
            "user_ip" => $ip,
            "user_agent" => $agent,
        ];
        $payload = http_build_query($params);

        // Run request
        $ch = curl_init(sprintf('https://%s.rest.akismet.com/1.1/comment-check', $key));
        curl_setopt($ch, CURLOPT_USERAGENT, "Phproject | Akismet/3.17");
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        return $result == "true";
    }

    /**
     * Check if an Akismet key is valid
     */
    public function isKeyValid(string $key): key
    {
        // Build request object
        $url = \Base::instance()->get("site.url");
        $params = [
            "key" => $key,
            "blog" => $url,
        ];
        $payload = http_build_query($params);

        // Run request
        $ch = curl_init("https://rest.akismet.com/1.1/verify-key");
        curl_setopt($ch, CURLOPT_USERAGENT, "Phproject | Akismet/3.17");
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        return $result == "valid";
    }
}
