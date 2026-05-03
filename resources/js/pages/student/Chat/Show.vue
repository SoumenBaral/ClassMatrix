<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Bot, Mic, MicOff, Send, Trash2, Volume2, VolumeOff, User } from 'lucide-vue-next';
import { ref, nextTick, onMounted, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { useVoiceInput, useVoiceOutput } from '@/composables/useVoice';

type Message = { id: number; role: string; content: string; created_at: string };
type Chat = { id: number; title: string; subject_context: string | null };
type ChatListItem = { id: number; title: string; updated_at: string };

const props = defineProps<{
    chat: Chat;
    messages: Message[];
    chats: ChatListItem[];
}>();

const messagesContainer = ref<HTMLElement>();
const messageInput = ref('');
const sending = ref(false);
const autoSpeak = ref(false);

const { isListening, transcript, isSupported: voiceInputSupported, startListening, stopListening } = useVoiceInput();
const { isSpeaking, isSupported: voiceOutputSupported, speak, stopSpeaking } = useVoiceOutput();

function scrollToBottom() {
    nextTick(() => {
        if (messagesContainer.value) {
            messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
        }
    });
}

onMounted(scrollToBottom);
watch(() => props.messages.length, scrollToBottom);

function sendMessage() {
    const msg = messageInput.value.trim();
    if (!msg || sending.value) return;

    sending.value = true;
    messageInput.value = '';

    router.post(`/student/chat/${props.chat.id}/send`, { message: msg }, {
        preserveScroll: true,
        onSuccess: () => {
            scrollToBottom();
            // Auto-speak the last AI message
            if (autoSpeak.value && props.messages.length > 0) {
                const lastMsg = props.messages[props.messages.length - 1];
                if (lastMsg.role === 'assistant') {
                    speak(lastMsg.content);
                }
            }
        },
        onFinish: () => { sending.value = false; },
    });
}

function toggleVoiceInput() {
    if (isListening.value) {
        stopListening();
    } else {
        startListening((text) => {
            messageInput.value = text;
            // Auto-send after voice input
            nextTick(() => sendMessage());
        });
    }
}

function speakMessage(content: string) {
    if (isSpeaking.value) {
        stopSpeaking();
    } else {
        speak(content);
    }
}

function deleteChat() {
    if (confirm('Delete this entire conversation?')) {
        router.delete(`/student/chat/${props.chat.id}`);
    }
}

function newChat() {
    router.post('/student/chat');
}

// Simple markdown-like rendering
function formatMessage(content: string): string {
    return content
        .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
        .replace(/\*(.+?)\*/g, '<em>$1</em>')
        .replace(/`([^`]+)`/g, '<code class="rounded bg-muted px-1 py-0.5 text-xs font-mono">$1</code>')
        .replace(/^- (.+)/gm, '<li class="ml-4">$1</li>')
        .replace(/^(\d+)\. (.+)/gm, '<li class="ml-4 list-decimal">$2</li>')
        .replace(/\n/g, '<br>');
}
</script>

<template>
    <Head :title="chat.title" />
    <div class="flex h-full flex-1 flex-col">
        <!-- Header -->
        <div class="flex items-center gap-3 border-b px-4 py-3">
            <Button variant="ghost" size="sm" as-child>
                <a href="/student/chat"><ArrowLeft class="size-4" /></a>
            </Button>
            <div class="flex items-center gap-2 flex-1 min-w-0">
                <div class="rounded-full bg-orange-100 p-1.5 dark:bg-orange-900">
                    <Bot class="size-4 text-orange-600" />
                </div>
                <div class="min-w-0">
                    <div class="text-sm font-medium truncate">{{ chat.title }}</div>
                    <div class="text-xs text-muted-foreground">AI Personal Teacher</div>
                </div>
            </div>
            <div class="flex items-center gap-1">
                <!-- Auto-speak toggle -->
                <Button v-if="voiceOutputSupported" variant="ghost" size="sm"
                    :class="autoSpeak ? 'text-orange-500' : ''"
                    @click="autoSpeak = !autoSpeak"
                    :title="autoSpeak ? 'Auto-speak ON' : 'Auto-speak OFF'">
                    <Volume2 v-if="autoSpeak" class="size-4" />
                    <VolumeOff v-else class="size-4" />
                </Button>
                <Button variant="ghost" size="sm" @click="newChat">New Chat</Button>
                <Button variant="ghost" size="sm" class="text-destructive" @click="deleteChat">
                    <Trash2 class="size-4" />
                </Button>
            </div>
        </div>

        <!-- Chat sidebar (mobile hidden, desktop visible) -->
        <div class="flex flex-1 overflow-hidden">
            <!-- Sidebar -->
            <div class="hidden lg:flex w-64 flex-col border-r bg-muted/30 overflow-y-auto">
                <div class="p-3 text-xs font-semibold text-muted-foreground">Conversations</div>
                <div v-for="c in chats" :key="c.id"
                    :class="['px-3 py-2 text-sm cursor-pointer transition-colors', c.id === chat.id ? 'bg-accent font-medium' : 'hover:bg-accent/50']"
                    @click="router.get(`/student/chat/${c.id}`)">
                    <div class="truncate">{{ c.title }}</div>
                    <div class="text-xs text-muted-foreground">{{ c.updated_at }}</div>
                </div>
            </div>

            <!-- Messages area -->
            <div class="flex flex-1 flex-col">
                <div ref="messagesContainer" class="flex-1 overflow-y-auto p-4 space-y-4">
                    <!-- Welcome message if no messages -->
                    <div v-if="!messages.length" class="flex flex-col items-center justify-center h-full text-center p-8">
                        <div class="rounded-full bg-orange-100 p-6 mb-4 dark:bg-orange-900">
                            <Bot class="size-10 text-orange-600" />
                        </div>
                        <h3 class="text-lg font-semibold mb-2">Hi! I'm your personal teacher</h3>
                        <p class="text-sm text-muted-foreground max-w-md">
                            Ask me anything about your subjects. I'll explain concepts, solve problems step-by-step,
                            and help you prepare for exams. You can type or use the microphone to speak!
                        </p>
                        <div class="flex flex-wrap gap-2 mt-6">
                            <button @click="messageInput = 'Explain quadratic equations in simple terms'; sendMessage()"
                                class="rounded-lg border px-3 py-2 text-sm hover:bg-accent transition-colors">
                                Explain quadratic equations
                            </button>
                            <button @click="messageInput = 'Help me solve: What is the area of a circle with radius 7cm?'; sendMessage()"
                                class="rounded-lg border px-3 py-2 text-sm hover:bg-accent transition-colors">
                                Solve a math problem
                            </button>
                            <button @click="messageInput = 'Quiz me on photosynthesis'; sendMessage()"
                                class="rounded-lg border px-3 py-2 text-sm hover:bg-accent transition-colors">
                                Quiz me on Science
                            </button>
                        </div>
                    </div>

                    <!-- Messages -->
                    <div v-for="msg in messages" :key="msg.id"
                        :class="['flex gap-3', msg.role === 'user' ? 'justify-end' : '']">
                        <!-- AI avatar -->
                        <div v-if="msg.role === 'assistant'" class="shrink-0 mt-1">
                            <div class="rounded-full bg-orange-100 p-1.5 dark:bg-orange-900">
                                <Bot class="size-3.5 text-orange-600" />
                            </div>
                        </div>

                        <!-- Message bubble -->
                        <div :class="[
                            'max-w-[80%] rounded-2xl px-4 py-3 text-sm leading-relaxed',
                            msg.role === 'user'
                                ? 'bg-primary text-primary-foreground rounded-br-md'
                                : 'bg-muted rounded-bl-md',
                        ]">
                            <div v-if="msg.role === 'assistant'" v-html="formatMessage(msg.content)" class="prose-sm" />
                            <div v-else>{{ msg.content }}</div>

                            <div class="flex items-center justify-between mt-1.5">
                                <span class="text-xs opacity-50">{{ msg.created_at }}</span>
                                <!-- Speak button for AI messages -->
                                <button v-if="msg.role === 'assistant' && voiceOutputSupported"
                                    @click="speakMessage(msg.content)"
                                    class="text-xs opacity-50 hover:opacity-100 ml-2 p-0.5">
                                    <Volume2 class="size-3" />
                                </button>
                            </div>
                        </div>

                        <!-- User avatar -->
                        <div v-if="msg.role === 'user'" class="shrink-0 mt-1">
                            <div class="rounded-full bg-primary p-1.5">
                                <User class="size-3.5 text-primary-foreground" />
                            </div>
                        </div>
                    </div>

                    <!-- Typing indicator -->
                    <div v-if="sending" class="flex gap-3">
                        <div class="shrink-0 mt-1">
                            <div class="rounded-full bg-orange-100 p-1.5 dark:bg-orange-900">
                                <Bot class="size-3.5 text-orange-600" />
                            </div>
                        </div>
                        <div class="bg-muted rounded-2xl rounded-bl-md px-4 py-3">
                            <div class="flex gap-1">
                                <span class="size-2 rounded-full bg-muted-foreground/40 animate-bounce" style="animation-delay: 0ms" />
                                <span class="size-2 rounded-full bg-muted-foreground/40 animate-bounce" style="animation-delay: 150ms" />
                                <span class="size-2 rounded-full bg-muted-foreground/40 animate-bounce" style="animation-delay: 300ms" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Voice transcript preview -->
                <div v-if="isListening && transcript" class="mx-4 mb-2 rounded-lg bg-orange-50 dark:bg-orange-950/30 px-3 py-2 text-sm text-orange-700 dark:text-orange-300">
                    <span class="animate-pulse mr-1">*</span> {{ transcript }}
                </div>

                <!-- Input area -->
                <div class="border-t p-4">
                    <div class="flex items-end gap-2 max-w-4xl mx-auto">
                        <!-- Voice input button -->
                        <Button v-if="voiceInputSupported"
                            :variant="isListening ? 'default' : 'outline'"
                            size="sm"
                            @click="toggleVoiceInput"
                            :class="isListening ? 'bg-red-500 hover:bg-red-600 animate-pulse' : ''">
                            <Mic v-if="!isListening" class="size-4" />
                            <MicOff v-else class="size-4" />
                        </Button>

                        <!-- Text input -->
                        <div class="flex-1 relative">
                            <textarea
                                v-model="messageInput"
                                @keydown.enter.exact.prevent="sendMessage"
                                placeholder="Type your question or click the mic to speak..."
                                rows="1"
                                class="flex w-full rounded-xl border border-input bg-transparent px-4 py-3 pr-12 text-sm shadow-xs transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring resize-none"
                                :disabled="sending"
                            />
                        </div>

                        <!-- Send button -->
                        <Button @click="sendMessage" :disabled="!messageInput.trim() || sending" size="sm">
                            <Send class="size-4" />
                        </Button>
                    </div>
                    <p class="text-xs text-center text-muted-foreground mt-2">
                        Press Enter to send. Click <Mic class="size-3 inline" /> to speak.
                        Click <Volume2 class="size-3 inline" /> on any message to hear it.
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
