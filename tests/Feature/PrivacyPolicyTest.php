<?php

namespace Tests\Feature;

use Tests\TestCase;

class PrivacyPolicyTest extends TestCase
{
    public function test_privacy_policy_is_public_and_shows_store_contact_details(): void
    {
        config(['shop.contact.email' => 'privacy@timepass.test']);

        $this->get('/privacy-policy')->assertOk()
            ->assertSee('<link rel="canonical" href="'.route('privacy').'">', false)
            ->assertSee('id="delete-account"', false)
            ->assertSee('privacy@timepass.test')
            ->assertSee('Razorpay');
    }

    public function test_footer_links_to_the_privacy_policy(): void
    {
        $this->get('/about')->assertOk()
            ->assertSee('href="'.route('privacy').'"', false);
    }

    public function test_deletion_link_preselects_the_contact_topic(): void
    {
        $this->get('/contact?subject='.rawurlencode('Privacy or account deletion'))->assertOk()
            ->assertSee('<option value="Privacy or account deletion" selected>', false);
    }
}
