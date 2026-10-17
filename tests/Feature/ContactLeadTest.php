<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactLeadTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    public function test_contact_form_saves_a_lead_and_shows_the_thank_you_message(): void
    {
        $this->post('/contact', [
            'name' => 'Priya Shah',
            'email' => 'priya@example.com',
            'phone' => '+91 98765 43210',
            'subject' => 'Product question',
            'message' => 'Is the serum safe for sensitive skin?',
        ])->assertRedirect('/contact');

        $lead = ContactMessage::sole();
        $this->assertSame('Priya Shah', $lead->name);
        $this->assertSame('+91 98765 43210', $lead->phone);
        $this->assertSame('Product question', $lead->subject);
        $this->assertSame('new', $lead->status);
        $this->assertNull($lead->read_at);

        $this->get('/contact')
            ->assertOk()
            ->assertSee('Thank you, Priya Shah!')
            ->assertSee('Our team will contact you soon')
            ->assertSee('priya@example.com')
            ->assertDontSee('name="message"', false);
    }

    public function test_phone_and_subject_are_optional_but_validated(): void
    {
        $this->post('/contact', ['name' => 'A', 'email' => 'a@example.com', 'message' => 'Hello there'])
            ->assertRedirect('/contact');
        $this->assertSame(1, ContactMessage::count());

        $this->from('/contact')->post('/contact', [
            'name' => 'B', 'email' => 'b@example.com', 'message' => 'Hello there',
            'phone' => 'call me maybe', 'subject' => 'Not a real topic',
        ])->assertSessionHasErrors(['phone', 'subject']);
        $this->assertSame(1, ContactMessage::count());
    }

    public function test_logged_in_customer_is_linked_and_form_is_prefilled(): void
    {
        $customer = User::factory()->create(['name' => 'Rohan Verma', 'phone' => '9876543210']);

        $this->actingAs($customer)->get('/contact')
            ->assertSee('value="Rohan Verma"', false)
            ->assertSee('value="9876543210"', false);

        $this->actingAs($customer)->post('/contact', [
            'name' => 'Rohan Verma', 'email' => $customer->email, 'message' => 'Where is my order?',
        ]);

        $this->assertSame($customer->id, ContactMessage::sole()->user_id);
    }

    public function test_admin_sees_leads_with_unread_badge_and_full_details(): void
    {
        $lead = ContactMessage::create([
            'name' => 'Priya Shah', 'email' => 'priya@example.com', 'phone' => '98765 43210',
            'subject' => 'Bulk or business enquiry', 'message' => 'We want 200 units for our salon.',
        ]);
        $admin = $this->admin();

        $this->actingAs($admin)->get('/admin/messages')
            ->assertOk()
            ->assertSee('Contact Leads')
            ->assertSee('Priya Shah')
            ->assertSee('98765 43210')
            ->assertSee('Bulk or business enquiry')
            ->assertSee('Unread');

        $this->actingAs($admin)->get("/admin/messages/{$lead->id}")
            ->assertOk()
            ->assertSee('We want 200 units for our salon.')
            ->assertSee('priya@example.com')
            ->assertSee('https://wa.me/919876543210', false)
            ->assertSee('tel:+919876543210', false);

        $this->assertNotNull($lead->fresh()->read_at);

        $this->actingAs($admin)->get('/admin')->assertOk()->assertSee('Recent contact leads')->assertSee('Priya Shah');
    }

    public function test_admin_can_filter_search_and_update_status(): void
    {
        $a = ContactMessage::create(['name' => 'Alpha', 'email' => 'alpha@example.com', 'message' => 'First lead']);
        ContactMessage::create(['name' => 'Beta', 'email' => 'beta@example.com', 'phone' => '5551234', 'message' => 'Second lead']);
        $admin = $this->admin();

        $this->actingAs($admin)->patch("/admin/messages/{$a->id}/status", ['status' => 'contacted'])
            ->assertSessionHas('status', 'Lead marked as Contacted.');
        $this->assertSame('contacted', $a->fresh()->status);

        $this->actingAs($admin)->patch("/admin/messages/{$a->id}/status", ['status' => 'bogus'])
            ->assertSessionHasErrors('status');

        $this->actingAs($admin)->get('/admin/messages?status=contacted')
            ->assertSee('Alpha')->assertDontSee('beta@example.com');

        $this->actingAs($admin)->get('/admin/messages?q=5551234')
            ->assertSee('Beta')->assertDontSee('alpha@example.com');
    }

    public function test_home_card_plus_button_opens_the_products_category(): void
    {
        $html = view('partials.product-card-premium', ['product' => [
            'id' => 1, 'name' => 'Keratin Smooth Shampoo', 'category' => 'Hair Care', 'category_slug' => 'hair-care',
            'price' => 549, 'mrp' => 699, 'image' => null, 'url' => '/product/keratin',
        ]])->render();

        $this->assertStringContainsString('href="'.route('products', ['category' => 'hair-care']).'"', $html);
        $this->assertStringNotContainsString(route('contact'), $html);
    }

    public function test_leads_page_requires_an_admin(): void
    {
        $this->get('/admin/messages')->assertRedirect('/admin/login');
        $this->actingAs(User::factory()->create())->get('/admin/messages')->assertRedirect('/admin/login');
    }
}
