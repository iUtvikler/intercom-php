<?php

namespace Intercom;

class ConversationTest extends IntercomTestCase
{
    public function testGetMessage()
    {
        $this->setMockResponse($this->client, 'Conversation/Message.txt');
        $response = $this->client->createMessage([
            'message_type' => 'email',
            'subject' => 'Hey',
            'body' => 'An important message',
            'from'=> [
                'type'=> 'admin',
                'id' => '25'
            ],
            'to' => [
                'type' => 'user',
                'id' => '536e564f316c83104c000020'
            ]
        ]);

        $this->assertBasicAuth('my-app', '1234');
        $this->assertRequest('POST', '/messages');

        $this->assertInstanceOf('\Guzzle\Service\Resource\Model', $response);
        $this->assertEquals('wash@serenity.io', $response['owner']['email']);
        $this->assertEquals('An important message', $response['body']);
    }

    public function testGetConversation()
    {
        $this->setMockResponse($this->client, 'Conversation/Conversation.txt');
        $response = $this->client->getConversation(['id' => '123456']);

        $this->assertRequest('GET', '/conversations/123456');

        $this->assertInstanceOf('\Guzzle\Service\Resource\Model', $response);
        $this->assertEquals('25', $response['conversation_message']['author']['id']);
        $this->assertEquals(true, $response['open']);
    }

    public function testGetConversations()
    {
        $this->setMockResponse($this->client, 'Conversation/ConversationList.txt');
        $response = $this->client->getConversations();
        $conversations = $response->get('conversations');

        $this->assertRequest('GET', '/conversations?type=user');

        $this->assertInstanceOf('\Guzzle\Service\Resource\Model', $response);
        $this->assertEquals(1, count($conversations));
        $this->assertEquals(1400850973, $conversations['0']['created_at']);
        $this->assertEquals('536e564f316c83104c000020', $conversations['0']['user']['id']);
        $this->assertEquals('admin', $conversations['0']['assignee']['type']);
    }

    public function testReplyToConversation()
    {
        $this->setMockResponse($this->client, 'Conversation/Conversation.txt');
        $this->client->replyToConversation(['id' => '123456']);

        $this->assertRequest('POST', '/conversations/123456/reply?type=json');
    }

    public function testMarkConversationAsRead()
    {
        $this->setMockResponse($this->client, 'Conversation/Conversation.txt');
        $this->client->markConversationAsRead(['id' => '123456', 'read' => true]);

        $this->assertRequest('PUT', '/conversations/123456');
    }

    /**
     * @expectedException \Guzzle\Service\Exception\ValidationException
     */
    public function testGetConversationNoID()
    {
        $this->client->getConversation();
    }

    /**
     * @expectedException \Guzzle\Service\Exception\ValidationException
     */
    public function testReplyConversationNoID()
    {
        $this->client->replyToConversation();
    }

    /**
     * @expectedException \Guzzle\Service\Exception\ValidationException
     */
    public function testMarkConversationAsReadNoID()
    {
        $this->client->markConversationAsRead();
    }
}