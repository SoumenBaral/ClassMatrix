<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import {
    ArrowLeft, Bot, Check, Copy, Mic, MicOff, Plus, RotateCcw,
    Send, Sparkles, Trash2, Volume2, VolumeOff, User, AlertTriangle,
} from 'lucide-vue-next'
import { ref, nextTick, onMounted, watch, computed } from 'vue'
import { useVoiceInput, useVoiceOutput } from '@/composables/useVoice'

type Message = { id: number; role: string; content: string; created_at: string }
type Chat = { id: number; title: string; subject_context: string | null }
type ChatListItem = { id: number; title: string; updated_at: string }

const props = defineProps<{
    chat: Chat
    messages: Message[]
    chats: ChatListItem[]
}>()

const messagesContainer = ref<HTMLElement>()
const textareaRef = ref<HTMLTextAreaElement>()
const messageInput = ref('')
const sending = ref(false)
const autoSpeak = ref(false)
const copiedId = ref<number | string | null>(null)

// Optimistic UI
const optimisticMessages = ref<{ role: string; content: string; created_at: string }[]>([])
const allMessages = ref<(Message | { role: string; content: string; created_at: string })[]>([])

function syncMessages() {
    optimisticMessages.value = []
    allMessages.value = [...props.messages]
}

onMounted(syncMessages)
watch(() => props.messages, syncMessages, { deep: true })

const { isListening, transcript, isSupported: voiceInputSupported, startListening, stopListening } = useVoiceInput()
const { isSpeaking, isSupported: voiceOutputSupported, speak, stopSpeaking } = useVoiceOutput()

function scrollToBottom() {
    nextTick(() => {
        if (messagesContainer.value) {
            messagesContainer.value.scrollTo({ top: messagesContainer.value.scrollHeight, behavior: 'smooth' })
        }
    })
}

onMounted(scrollToBottom)
watch(() => allMessages.value.length, scrollToBottom)

// Auto-resize textarea
function autoResize() {
    if (!textareaRef.value) return
    textareaRef.value.style.height = 'auto'
    textareaRef.value.style.height = Math.min(textareaRef.value.scrollHeight, 150) + 'px'
}

watch(messageInput, () => nextTick(autoResize))

function sendMessage() {
    const msg = messageInput.value.trim()
    if (!msg || sending.value) return

    sending.value = true
    messageInput.value = ''
    nextTick(autoResize)

    const now = new Date()
    const timeStr = `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}`
    optimisticMessages.value.push({ role: 'user', content: msg, created_at: timeStr })
    allMessages.value = [...props.messages, ...optimisticMessages.value]
    scrollToBottom()

    router.post(`/student/chat/${props.chat.id}/send`, { message: msg }, {
        preserveScroll: true,
        onSuccess: () => {
            scrollToBottom()
            if (autoSpeak.value && props.messages.length > 0) {
                const lastMsg = props.messages[props.messages.length - 1]
                if (lastMsg.role === 'assistant') speak(lastMsg.content)
            }
        },
        onError: () => {
            optimisticMessages.value.push({
                role: 'assistant',
                content: "⚠️ **Request Failed**\n\nCouldn't reach the server. Check your connection and try again.",
                created_at: timeStr,
            })
            allMessages.value = [...props.messages, ...optimisticMessages.value]
        },
        onFinish: () => { sending.value = false },
    })
}

function resendLast() {
    const lastUserMsg = [...allMessages.value].reverse().find(m => m.role === 'user')
    if (lastUserMsg) {
        messageInput.value = lastUserMsg.content
        sendMessage()
    }
}

function toggleVoiceInput() {
    if (isListening.value) {
        stopListening()
    } else {
        startListening((text) => {
            messageInput.value = text
            nextTick(() => sendMessage())
        })
    }
}

function speakMessage(content: string) {
    isSpeaking.value ? stopSpeaking() : speak(content)
}

function copyMessage(content: string, id: number | string) {
    navigator.clipboard.writeText(content.replace(/<[^>]*>/g, ''))
    copiedId.value = id
    setTimeout(() => { copiedId.value = null }, 2000)
}

function deleteChat() {
    if (confirm('Delete this entire conversation?')) {
        router.delete(`/student/chat/${props.chat.id}`)
    }
}

function newChat() {
    router.post('/student/chat')
}

function isErrorMessage(content: string): boolean {
    // Only match our exact error format — never flag legitimate AI responses
    return content.startsWith('⚠️ **')
}

// Rich markdown rendering
function formatMessage(content: string): string {
    let html = content
        // Code blocks (```lang ... ```)
        .replace(/```(\w*)\n([\s\S]*?)```/g, (_match, lang, code) => {
            const escaped = code.replace(/</g, '&lt;').replace(/>/g, '&gt;').trimEnd()
            return `<div class="my-3 rounded-xl overflow-hidden border border-border/50"><div class="flex items-center justify-between bg-muted/60 px-4 py-1.5 text-[11px] text-muted-foreground font-mono"><span>${lang || 'code'}</span></div><pre class="bg-muted/30 px-4 py-3 overflow-x-auto text-[13px] leading-relaxed"><code>${escaped}</code></pre></div>`
        })
        // Headings
        .replace(/^### (.+)/gm, '<h4 class="font-bold text-base mt-4 mb-2">$1</h4>')
        .replace(/^## (.+)/gm, '<h3 class="font-bold text-lg mt-4 mb-2">$1</h3>')
        // Bold + italic
        .replace(/\*\*(.+?)\*\*/g, '<strong class="font-semibold text-foreground">$1</strong>')
        .replace(/\*(.+?)\*/g, '<em>$1</em>')
        // Inline code
        .replace(/`([^`]+)`/g, '<code class="rounded-md bg-primary/10 px-1.5 py-0.5 text-[13px] font-mono text-primary">$1</code>')
        // Unordered lists
        .replace(/^- (.+)/gm, '<li class="ml-5 list-disc my-0.5">$1</li>')
        // Ordered lists
        .replace(/^(\d+)\. (.+)/gm, '<li class="ml-5 list-decimal my-0.5">$2</li>')
        // Horizontal rule
        .replace(/^---$/gm, '<hr class="my-4 border-border/30" />')
        // Newlines
        .replace(/\n/g, '<br>')
        // Clean up consecutive <br> after block elements
        .replace(/(<\/(?:h[34]|pre|div|hr|li)>)<br>/g, '$1')
        .replace(/<br>(<(?:h[34]|div|hr))/g, '$1')

    return html
}

const quickPrompts = [
    { emoji: '📐', text: 'Explain a math concept', full: 'Explain quadratic equations with step-by-step examples' },
    { emoji: '🔬', text: 'Science question', full: 'Explain photosynthesis in simple terms with a diagram description' },
    { emoji: '📝', text: 'Help with essay', full: 'Help me write an essay outline about climate change' },
    { emoji: '🧪', text: 'Solve a problem', full: 'Solve this step by step: A car travels 120km in 2 hours. What is the average speed?' },
    { emoji: '📚', text: 'Quiz me', full: 'Give me 5 multiple choice questions about World War 2' },
    { emoji: '💡', text: 'Study tips', full: 'Give me effective study tips for preparing for final exams' },
]

const messageCount = computed(() => allMessages.value.length)
</script>

<template>
    <Head :title="chat.title" />
    <div class="flex h-full flex-1 flex-col">
        <!-- ===== Header ===== -->
        <div class="flex items-center gap-3 border-b border-border bg-card/80 backdrop-blur-xl px-4 py-3 z-10">
            <a href="/student/chat" class="rounded-lg p-2 text-muted-foreground transition-colors hover:bg-accent hover:text-foreground">
                <ArrowLeft class="size-4" />
            </a>

            <div class="flex items-center gap-3 flex-1 min-w-0">
                <div class="relative">
                    <div class="flex size-9 items-center justify-center rounded-full bg-linear-to-br from-brand-blue to-brand-cyan shadow-sm">
                        <Bot class="size-4 text-white" />
                    </div>
                    <div :class="[
                        'absolute -bottom-0.5 -right-0.5 size-2.5 rounded-full border-2 border-card transition-colors',
                        sending ? 'bg-amber-400 animate-pulse' : 'bg-emerald-400',
                    ]" />
                </div>
                <div class="min-w-0">
                    <div class="text-sm font-semibold truncate">{{ chat.title }}</div>
                    <div :class="['text-xs transition-colors', sending ? 'text-amber-500' : 'text-emerald-500']">
                        {{ sending ? 'Thinking...' : 'AI Teacher · Online' }}
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-0.5">
                <button v-if="voiceOutputSupported"
                    :class="['rounded-lg p-2 text-xs transition-all', autoSpeak ? 'text-brand-cyan bg-brand-cyan/10' : 'text-muted-foreground hover:bg-accent']"
                    @click="autoSpeak = !autoSpeak"
                    :title="autoSpeak ? 'Auto-speak ON' : 'Auto-speak OFF'"
                >
                    <Volume2 v-if="autoSpeak" class="size-4" />
                    <VolumeOff v-else class="size-4" />
                </button>
                <button class="rounded-lg p-2 text-muted-foreground transition-colors hover:bg-accent hover:text-foreground" @click="newChat" title="New chat">
                    <Plus class="size-4" />
                </button>
                <button class="rounded-lg p-2 text-muted-foreground transition-colors hover:bg-destructive/10 hover:text-destructive" @click="deleteChat" title="Delete chat">
                    <Trash2 class="size-4" />
                </button>
            </div>
        </div>

        <div class="flex flex-1 overflow-hidden">
            <!-- ===== Sidebar ===== -->
            <div class="hidden lg:flex w-60 flex-col border-r border-border bg-muted/10 overflow-y-auto">
                <div class="p-4 pb-2 text-xs font-semibold text-muted-foreground uppercase tracking-wider">Chats</div>
                <a
                    v-for="c in chats"
                    :key="c.id"
                    :href="`/student/chat/${c.id}`"
                    :class="[
                        'block px-4 py-2.5 text-sm transition-all border-l-2',
                        c.id === chat.id
                            ? 'bg-accent border-l-primary font-medium'
                            : 'border-l-transparent hover:bg-accent/50',
                    ]"
                >
                    <div class="truncate text-[13px]">{{ c.title }}</div>
                    <div class="text-[11px] text-muted-foreground mt-0.5">{{ c.updated_at }}</div>
                </a>
            </div>

            <!-- ===== Main chat area ===== -->
            <div class="flex flex-1 flex-col bg-background">
                <div ref="messagesContainer" class="flex-1 overflow-y-auto scroll-smooth">
                    <!-- ===== Welcome state ===== -->
                    <div v-if="!messageCount && !sending" class="flex flex-col items-center justify-center h-full text-center px-6 py-12">
                        <div class="relative mb-8">
                            <div class="flex size-20 items-center justify-center rounded-2xl bg-linear-to-br from-brand-blue to-brand-cyan shadow-xl shadow-brand-blue/20">
                                <Bot class="size-10 text-white" />
                            </div>
                            <div class="absolute -top-1 -right-1 flex size-6 items-center justify-center rounded-full bg-amber-400 shadow-sm">
                                <Sparkles class="size-3 text-white" />
                            </div>
                        </div>
                        <h3 class="text-xl font-bold mb-2">Hi{{ chat.subject_context ? `, let's study ${chat.subject_context}` : '! How can I help you learn today' }}?</h3>
                        <p class="text-sm text-muted-foreground max-w-md mb-10 leading-relaxed">
                            I can explain concepts, solve problems step-by-step, write essays, quiz you, and help you prepare for exams. Try one of these:
                        </p>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 max-w-lg">
                            <button
                                v-for="prompt in quickPrompts"
                                :key="prompt.text"
                                @click="messageInput = prompt.full; sendMessage()"
                                class="flex items-center gap-2.5 rounded-xl border border-border bg-card px-4 py-3 text-left text-sm transition-all hover:border-primary/30 hover:bg-accent hover:-translate-y-0.5 hover:shadow-sm"
                            >
                                <span class="text-lg">{{ prompt.emoji }}</span>
                                <span class="text-muted-foreground">{{ prompt.text }}</span>
                            </button>
                        </div>
                    </div>

                    <!-- ===== Messages list ===== -->
                    <div v-else class="px-4 py-6 space-y-6 max-w-3xl mx-auto">
                        <div
                            v-for="(msg, idx) in allMessages"
                            :key="'id' in msg ? msg.id : `opt-${idx}`"
                            :class="['flex gap-3 group', msg.role === 'user' ? 'justify-end' : '']"
                        >
                            <!-- AI avatar -->
                            <div v-if="msg.role === 'assistant'" class="shrink-0 mt-1">
                                <div :class="[
                                    'flex size-8 items-center justify-center rounded-full',
                                    isErrorMessage(msg.content)
                                        ? 'bg-destructive/10'
                                        : 'bg-linear-to-br from-brand-blue/15 to-brand-cyan/15',
                                ]">
                                    <AlertTriangle v-if="isErrorMessage(msg.content)" class="size-4 text-destructive" />
                                    <Bot v-else class="size-4 text-brand-cyan" />
                                </div>
                            </div>

                            <!-- Message bubble -->
                            <div :class="[
                                'relative rounded-2xl text-sm leading-relaxed',
                                msg.role === 'user'
                                    ? 'max-w-[80%] bg-primary text-primary-foreground rounded-br-md px-4 py-3'
                                    : isErrorMessage(msg.content)
                                        ? 'max-w-[88%] lg:max-w-[82%] bg-destructive/5 border border-destructive/15 rounded-bl-md px-4 py-3'
                                        : 'max-w-[88%] lg:max-w-[82%] bg-muted/50 rounded-bl-md px-5 py-4',
                            ]">
                                <!-- AI message with rich rendering -->
                                <div
                                    v-if="msg.role === 'assistant'"
                                    v-html="formatMessage(msg.content)"
                                    class="ai-message [&_li]:my-0.5 [&_h3]:first:mt-0 [&_h4]:first:mt-0 [&_strong]:text-foreground"
                                />
                                <!-- User message -->
                                <div v-else class="whitespace-pre-wrap">{{ msg.content }}</div>

                                <!-- Actions bar -->
                                <div class="flex items-center gap-1 mt-2.5 text-xs opacity-0 group-hover:opacity-100 transition-opacity">
                                    <span class="opacity-50 mr-1">{{ msg.created_at }}</span>
                                    <!-- Copy -->
                                    <button
                                        v-if="msg.role === 'assistant' && !isErrorMessage(msg.content)"
                                        @click="copyMessage(msg.content, 'id' in msg ? msg.id : idx)"
                                        class="rounded-md p-1 text-muted-foreground hover:bg-accent hover:text-foreground transition-colors"
                                        title="Copy"
                                    >
                                        <Check v-if="copiedId === ('id' in msg ? msg.id : idx)" class="size-3 text-emerald-500" />
                                        <Copy v-else class="size-3" />
                                    </button>
                                    <!-- Speak -->
                                    <button
                                        v-if="msg.role === 'assistant' && voiceOutputSupported && !isErrorMessage(msg.content)"
                                        @click="speakMessage(msg.content)"
                                        class="rounded-md p-1 text-muted-foreground hover:bg-accent hover:text-foreground transition-colors"
                                        title="Read aloud"
                                    >
                                        <Volume2 class="size-3" />
                                    </button>
                                </div>
                            </div>

                            <!-- User avatar -->
                            <div v-if="msg.role === 'user'" class="shrink-0 mt-1">
                                <div class="flex size-8 items-center justify-center rounded-full bg-primary shadow-sm">
                                    <User class="size-4 text-primary-foreground" />
                                </div>
                            </div>
                        </div>

                        <!-- Typing indicator -->
                        <div v-if="sending" class="flex gap-3 animate-fade-in-up">
                            <div class="shrink-0 mt-1">
                                <div class="flex size-8 items-center justify-center rounded-full bg-linear-to-br from-brand-blue/15 to-brand-cyan/15">
                                    <Bot class="size-4 text-brand-cyan animate-pulse" />
                                </div>
                            </div>
                            <div class="bg-muted/50 rounded-2xl rounded-bl-md px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex gap-1.5">
                                        <span class="size-2 rounded-full bg-brand-cyan/50 animate-bounce" style="animation-delay: 0ms" />
                                        <span class="size-2 rounded-full bg-brand-cyan/50 animate-bounce" style="animation-delay: 150ms" />
                                        <span class="size-2 rounded-full bg-brand-cyan/50 animate-bounce" style="animation-delay: 300ms" />
                                    </div>
                                    <span class="text-xs text-muted-foreground">AI is thinking...</span>
                                </div>
                            </div>
                        </div>

                        <!-- Retry button after error -->
                        <div v-if="!sending && allMessages.length > 0 && isErrorMessage(allMessages[allMessages.length - 1].content)" class="flex justify-center">
                            <button @click="resendLast" class="inline-flex items-center gap-2 rounded-lg border border-border px-4 py-2 text-sm text-muted-foreground hover:bg-accent hover:text-foreground transition-colors">
                                <RotateCcw class="size-3.5" /> Retry last message
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Voice transcript -->
                <div v-if="isListening && transcript" class="mx-4 mb-2 rounded-xl bg-brand-cyan/5 border border-brand-cyan/20 px-4 py-2.5 text-sm text-brand-cyan">
                    <span class="inline-block size-2 rounded-full bg-red-500 animate-pulse mr-2" /> {{ transcript }}
                </div>

                <!-- ===== Input ===== -->
                <div class="border-t border-border bg-card/60 backdrop-blur-xl p-4">
                    <div class="flex items-end gap-2 max-w-3xl mx-auto">
                        <button
                            v-if="voiceInputSupported"
                            @click="toggleVoiceInput"
                            :disabled="sending"
                            :class="[
                                'flex-shrink-0 flex items-center justify-center size-10 rounded-xl transition-all',
                                isListening
                                    ? 'bg-red-500 text-white animate-pulse shadow-lg shadow-red-500/25'
                                    : 'border border-border text-muted-foreground hover:bg-accent hover:text-foreground disabled:opacity-30',
                            ]"
                            title="Voice input"
                        >
                            <Mic v-if="!isListening" class="size-4" />
                            <MicOff v-else class="size-4" />
                        </button>

                        <div class="flex-1 relative">
                            <textarea
                                ref="textareaRef"
                                v-model="messageInput"
                                @keydown.enter.exact.prevent="sendMessage"
                                :placeholder="sending ? 'Waiting for AI...' : 'Ask anything — math, science, essays, study tips...'"
                                rows="1"
                                class="flex w-full rounded-xl border border-border bg-background px-4 py-3 pr-12 text-sm transition-all placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary/50 resize-none disabled:opacity-40"
                                style="max-height: 150px"
                                :disabled="sending"
                            />
                        </div>

                        <button
                            @click="sendMessage"
                            :disabled="!messageInput.trim() || sending"
                            :class="[
                                'flex-shrink-0 flex items-center justify-center size-10 rounded-xl transition-all duration-200',
                                messageInput.trim() && !sending
                                    ? 'bg-primary text-primary-foreground shadow-md shadow-primary/20 hover:shadow-lg hover:brightness-110 hover:-translate-y-0.5'
                                    : 'bg-muted text-muted-foreground/30 cursor-not-allowed',
                            ]"
                        >
                            <Send class="size-4" />
                        </button>
                    </div>
                    <p class="text-[11px] text-center text-muted-foreground/30 mt-2.5">
                        Enter to send &middot; Shift+Enter for new line &middot; AI responses may not always be accurate
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.ai-message :deep(pre) {
    margin: 0;
    font-family: 'SF Mono', 'Fira Code', 'Fira Mono', Menlo, Consolas, monospace;
}
.ai-message :deep(code) {
    font-family: 'SF Mono', 'Fira Code', 'Fira Mono', Menlo, Consolas, monospace;
}
</style>
