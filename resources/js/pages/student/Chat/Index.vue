<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { Bot, MessageCircle, Plus, Trash2, Sparkles } from 'lucide-vue-next'
import { ref } from 'vue'

type ChatListItem = { id: number; title: string; subject_context: string | null; updated_at: string }

defineProps<{
    chats: ChatListItem[]
}>()

const creating = ref(false)

function newChat() {
    creating.value = true
    router.post('/student/chat', {}, {
        onFinish: () => { creating.value = false },
    })
}

function deleteChat(id: number, e: Event) {
    e.preventDefault()
    e.stopPropagation()
    if (confirm('Delete this conversation?')) {
        router.delete(`/student/chat/${id}`)
    }
}
</script>

<template>
    <Head title="AI Teacher" />
    <div class="flex h-full flex-1 flex-col p-6">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold flex items-center gap-3">
                    <div class="flex size-10 items-center justify-center rounded-xl bg-linear-to-br from-brand-blue to-brand-cyan">
                        <Bot class="size-5 text-white" />
                    </div>
                    AI Personal Teacher
                </h1>
                <p class="mt-2 text-sm text-muted-foreground">Your 24/7 study companion. Ask questions, solve problems, and learn at your own pace.</p>
            </div>
            <button
                @click="newChat"
                :disabled="creating"
                class="inline-flex items-center gap-2 rounded-xl bg-primary px-5 py-2.5 text-sm font-medium text-primary-foreground transition-all hover:opacity-90 disabled:opacity-50"
            >
                <Plus class="size-4" /> New Chat
            </button>
        </div>

        <!-- Chat list -->
        <div v-if="chats.length">
            <h3 class="text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-4">Recent Conversations</h3>
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                <Link
                    v-for="chat in chats"
                    :key="chat.id"
                    :href="`/student/chat/${chat.id}`"
                    class="group relative flex flex-col rounded-xl border border-border bg-card p-5 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg hover:border-primary/20"
                >
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex size-9 items-center justify-center rounded-lg bg-primary/10">
                            <MessageCircle class="size-4 text-primary" />
                        </div>
                        <button
                            @click="deleteChat(chat.id, $event)"
                            class="rounded-lg p-1.5 text-muted-foreground/30 opacity-0 transition-all group-hover:opacity-100 hover:bg-destructive/10 hover:text-destructive"
                        >
                            <Trash2 class="size-3.5" />
                        </button>
                    </div>
                    <h3 class="text-sm font-semibold line-clamp-2 group-hover:text-primary transition-colors">
                        {{ chat.title }}
                    </h3>
                    <div class="mt-auto pt-3 flex items-center justify-between text-xs text-muted-foreground">
                        <span v-if="chat.subject_context" class="rounded-md bg-muted px-2 py-0.5">{{ chat.subject_context }}</span>
                        <span class="ml-auto">{{ chat.updated_at }}</span>
                    </div>
                </Link>
            </div>
        </div>

        <!-- Empty state -->
        <div v-else class="flex flex-1 flex-col items-center justify-center text-center">
            <div class="relative mb-6">
                <div class="flex size-20 items-center justify-center rounded-2xl bg-linear-to-br from-brand-blue to-brand-cyan shadow-lg">
                    <Bot class="size-10 text-white" />
                </div>
                <div class="absolute -top-1 -right-1 flex size-6 items-center justify-center rounded-full bg-amber-400">
                    <Sparkles class="size-3 text-white" />
                </div>
            </div>
            <h3 class="text-xl font-bold mb-2">Meet Your AI Teacher</h3>
            <p class="text-sm text-muted-foreground max-w-md mb-8 leading-relaxed">
                Start a conversation to get help with any subject. I can explain concepts,
                solve problems step-by-step, quiz you, and help you prepare for exams.
            </p>
            <div class="flex flex-wrap justify-center gap-3 mb-8 text-sm">
                <span class="rounded-full border border-border bg-muted/50 px-4 py-1.5">Math</span>
                <span class="rounded-full border border-border bg-muted/50 px-4 py-1.5">Science</span>
                <span class="rounded-full border border-border bg-muted/50 px-4 py-1.5">English</span>
                <span class="rounded-full border border-border bg-muted/50 px-4 py-1.5">History</span>
                <span class="rounded-full border border-border bg-muted/50 px-4 py-1.5">& More</span>
            </div>
            <button
                @click="newChat"
                :disabled="creating"
                class="inline-flex items-center gap-2 rounded-xl bg-primary px-8 py-3 text-base font-semibold text-primary-foreground transition-all hover:opacity-90 disabled:opacity-50"
            >
                <Sparkles class="size-5" /> Start Your First Chat
            </button>
        </div>
    </div>
</template>
