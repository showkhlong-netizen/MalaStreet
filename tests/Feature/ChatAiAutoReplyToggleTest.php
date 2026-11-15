<?php

namespace Tests\Feature;

use App\Models\ChatMessage;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ChatAiAutoReplyToggleTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::create([
            'name' => 'Admin Test',
            'email' => 'admin-chat-test@malastreet.com',
            'password' => Hash::make('password123'),
        ]);
    }

    public function test_kitchen_chat_displays_ai_toggle_and_can_switch_on_off(): void
    {
        $viewRes = $this->actingAs($this->adminUser)->get('/kitchen/chat');
        $viewRes->assertStatus(200);
        $viewRes->assertSee('ตอบแชท AI อัตโนมัติ');

        $offRes = $this->actingAs($this->adminUser)->postJson('/kitchen/chat/toggle-ai', [
            'enabled' => false,
        ]);
        $offRes->assertStatus(200)->assertJson([
            'success' => true,
            'enabled' => false,
        ]);
        $this->assertSame('0', Setting::get('chat_ai_auto_reply_enabled'));

        $onRes = $this->actingAs($this->adminUser)->postJson('/kitchen/chat/toggle-ai', [
            'enabled' => true,
        ]);
        $onRes->assertStatus(200)->assertJson([
            'success' => true,
            'enabled' => true,
        ]);
        $this->assertSame('1', Setting::get('chat_ai_auto_reply_enabled'));
    }

    public function test_customer_chat_respects_ai_auto_reply_toggle_and_uses_order_total_amount(): void
    {
        $order = Order::create([
            'order_no' => 'MS-TEST-001',
            'queue_number' => 'G99',
            'order_type' => 'online',
            'customer_name' => 'คุณทดสอบ',
            'customer_phone' => '0890001111',
            'subtotal' => 120,
            'total_amount' => 120,
            'status' => 'confirmed',
        ]);

        // 1. Toggle OFF: no bot reply should be created
        Setting::set('chat_ai_auto_reply_enabled', '0', 'chat');

        $offReply = $this->postJson('/order/chat/send', [
            'message' => 'สอบถามออเดอร์ครับ',
            'order_no' => $order->order_no,
            'customer_name' => $order->customer_name,
            'customer_phone' => $order->customer_phone,
        ]);

        $offReply->assertStatus(200)->assertJson([
            'success' => true,
            'bot_reply' => null,
            'ai_auto_reply_enabled' => false,
        ]);

        $this->assertSame(1, ChatMessage::where('order_no', $order->order_no)->count());
        $this->assertSame(0, ChatMessage::where('order_no', $order->order_no)->where('sender', 'shop')->count());

        // 2. Toggle ON with Gemini API mocked: Gemini receives total_amount (120.00) and replies
        Setting::set('chat_ai_auto_reply_enabled', '1', 'chat');
        Setting::set('gemini_api_key', 'test-fake-key');

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => 'สวัสดีครับคิว G99 ยอด ฿120 ร้านกำลังดูแลให้นะครับ 🔥']
                            ]
                        ]
                    ]
                ]
            ], 200),
        ]);

        $onReply = $this->postJson('/order/chat/send', [
            'message' => 'คิว G99 ถึงไหนแล้วครับ',
            'order_no' => $order->order_no,
            'customer_name' => $order->customer_name,
            'customer_phone' => $order->customer_phone,
        ]);

        $onReply->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('ai_auto_reply_enabled', true)
            ->assertJsonPath('bot_reply.sender', 'shop')
            ->assertJsonPath('bot_reply.message', 'สวัสดีครับคิว G99 ยอด ฿120 ร้านกำลังดูแลให้นะครับ 🔥');

        Http::assertSent(function ($request) {
            $body = json_encode($request->data(), JSON_UNESCAPED_UNICODE);
            return str_contains($body, '120.00');
        });
    }
}
