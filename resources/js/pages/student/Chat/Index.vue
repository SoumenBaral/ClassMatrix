<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Bot, MessageCircle, Plus, Trash2 } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import Heading from '@/components/Heading.vue';

type Chat = { id: number; title: string; subject_context: string | null; updated_at: string };

const props = defineProps<{ chats: Chat[] }>();

const form = useForm({ subject_context: '' });

function newChat() {
    form.post('/student/chat');
}

function deleteChat(id: number) {
    if (confirm('Delete this conversation?')) {
        router.delete(`/student/chat/${id}`);
    }
}
</script>

<template>
    <Head title="AI Teacher" />
    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <Heading title="AI Personal Teacher" description="Ask questions, get explanations, solve problems. Speak or type!" />
            <Button @click="newChat"><Plus class="mr-2 size-4" /> New Chat</Button>
        </div>

        <!-- Welcome card -->
        <Card class="bg-gradient-to-r from-orange-50 to-amber-50 dark:from-orange-950/30 dark:to-amber-950/30 border-orange-200">
            <CardContent class="flex items-center gap-4 pt-6">
                <div class="rounded-full bg-orange-100 p-4 dark:bg-orange-900">
                    <Bot class="size-8 text-orange-600" />
                </div>
                <div>
                    <h3 class="font-semibold text-lg">Hello! I'm your AI Teacher</h3>
                    <p class="text-sm text-muted-foreground mt-1">
                        I can help you with any subject — Math, Science, English, and more.
                        Ask me questions, I'll explain step by step. You can even talk to me using your voice!
                    </p>
                </div>
            </CardContent>
        </Card>

        <!-- Chat list -->
        <div v-if="chats.length" class="space-y-2">
            <h3 class="text-sm font-semibold text-muted-foreground">Recent Conversations</h3>
            <div v-for="chat in chats" :key="chat.id"
                class="flex items-center justify-between rounded-lg border p-3 hover:bg-accent transition-colors cursor-pointer"
                @click="router.get(`/student/chat/${chat.id}`)">
                <div class="flex items-center gap-3 min-w-0">
                    <MessageCircle class="size-4 text-muted-foreground shrink-0" />
                    <div class="min-w-0">
                        <div class="text-sm font-medium truncate">{{ chat.title }}</div>
                        <div class="text-xs text-muted-foreground">{{ chat.updated_at }}</div>
                    </div>
                </div>
                <button @click.stop="deleteChat(chat.id)" class="text-muted-foreground hover:text-destructive shrink-0 p-1">
                    <Trash2 class="size-3" />
                </button>
            </div>
        </div>

        <div v-else class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed p-12 text-center">
            <Bot class="size-12 text-muted-foreground/30 mb-4" />
            <p class="text-muted-foreground mb-4">Start your first conversation with your AI Teacher.</p>
            <Button @click="newChat"><Plus class="mr-2 size-4" /> Start Chatting</Button>
        </div>
    </div>
</template>
