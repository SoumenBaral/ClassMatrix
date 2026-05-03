import { ref } from 'vue';

export function useVoiceInput() {
    const isListening = ref(false);
    const transcript = ref('');
    const isSupported = ref(typeof window !== 'undefined' && ('SpeechRecognition' in window || 'webkitSpeechRecognition' in window));

    let recognition: any = null;

    function startListening(onResult?: (text: string) => void) {
        if (!isSupported.value) return;

        const SpeechRecognition = (window as any).SpeechRecognition || (window as any).webkitSpeechRecognition;
        recognition = new SpeechRecognition();
        recognition.lang = 'en-US';
        recognition.interimResults = true;
        recognition.continuous = false;

        recognition.onstart = () => {
            isListening.value = true;
            transcript.value = '';
        };

        recognition.onresult = (event: any) => {
            let interim = '';
            let final = '';
            for (let i = event.resultIndex; i < event.results.length; i++) {
                const t = event.results[i][0].transcript;
                if (event.results[i].isFinal) {
                    final += t;
                } else {
                    interim += t;
                }
            }
            transcript.value = final || interim;
        };

        recognition.onend = () => {
            isListening.value = false;
            if (transcript.value && onResult) {
                onResult(transcript.value);
            }
        };

        recognition.onerror = () => {
            isListening.value = false;
        };

        recognition.start();
    }

    function stopListening() {
        if (recognition) {
            recognition.stop();
        }
    }

    return { isListening, transcript, isSupported, startListening, stopListening };
}

export function useVoiceOutput() {
    const isSpeaking = ref(false);
    const isSupported = ref(typeof window !== 'undefined' && 'speechSynthesis' in window);

    function speak(text: string) {
        if (!isSupported.value) return;

        // Strip markdown for cleaner speech
        const cleanText = text
            .replace(/[#*_~`]/g, '')
            .replace(/\[([^\]]+)\]\([^)]+\)/g, '$1')
            .replace(/\n+/g, '. ');

        window.speechSynthesis.cancel();

        const utterance = new SpeechSynthesisUtterance(cleanText);
        utterance.rate = 0.95;
        utterance.pitch = 1;

        // Prefer a natural-sounding voice
        const voices = window.speechSynthesis.getVoices();
        const preferred = voices.find(
            (v) => v.lang.startsWith('en') && (v.name.includes('Google') || v.name.includes('Natural') || v.name.includes('Samantha')),
        );
        if (preferred) utterance.voice = preferred;

        utterance.onstart = () => { isSpeaking.value = true; };
        utterance.onend = () => { isSpeaking.value = false; };
        utterance.onerror = () => { isSpeaking.value = false; };

        window.speechSynthesis.speak(utterance);
    }

    function stopSpeaking() {
        window.speechSynthesis.cancel();
        isSpeaking.value = false;
    }

    return { isSpeaking, isSupported, speak, stopSpeaking };
}
