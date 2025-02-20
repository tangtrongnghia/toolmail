<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from 'vue'

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    maxWidth: {
        type: String,
        default: '2xl',
    },
    closeable: {
        type: Boolean,
        default: true,
    },
})

const emit = defineEmits(['close'])
const dialog = ref()
const showSlot = ref(props.show)

watch(
    () => props.show,
    () => {
        if (props.show) {
            document.body.style.overflow = 'hidden'
            showSlot.value = true

            dialog.value?.showModal()
        } else {
            document.body.style.overflow = ''

            setTimeout(() => {
                dialog.value?.close()
                showSlot.value = false
            }, 200)
        }
    },
)

const close = () => {
    if (props.closeable) {
        emit('close')
    }
}

const closeOnEscape = (e) => {
    if (e.key === 'Escape') {
        e.preventDefault()

        if (props.show) {
            close()
        }
    }
}

onMounted(() => document.addEventListener('keydown', closeOnEscape))

onUnmounted(() => {
    document.removeEventListener('keydown', closeOnEscape)

    document.body.style.overflow = ''
})

const maxWidthClass = computed(() => {
    return {
        sm: 'sm:max-w-sm',
        md: 'sm:max-w-md',
        lg: 'sm:max-w-lg',
        xl: 'sm:max-w-xl',
        '2xl': 'sm:max-w-2xl',
    }[props.maxWidth]
})

defineExpose({
    close,
})
</script>

<template>
    <dialog
        ref="dialog"
        class="z-50 m-0 min-h-full min-w-full overflow-y-auto bg-transparent backdrop:bg-transparent"
    >
        <div
            class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0"
            scroll-region
        >
            <Transition
                enterActiveClass="ease-out duration-300"
                enterFromClass="opacity-0"
                enterToClass="opacity-100"
                leaveActiveClass="ease-in duration-200"
                leaveFromClass="opacity-100"
                leaveToClass="opacity-0"
            >
                <div
                    v-show="show"
                    class="fixed inset-0 transform transition-all"
                    @click="close"
                >
                    <div class="absolute inset-0 bg-gray-500 opacity-75 dark:bg-gray-900" />
                </div>
            </Transition>

            <Transition
                enterActiveClass="ease-out duration-300"
                enterFromClass="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                enterToClass="opacity-100 translate-y-0 sm:scale-100"
                leaveActiveClass="ease-in duration-200"
                leaveFromClass="opacity-100 translate-y-0 sm:scale-100"
                leaveToClass="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            >
                <div
                    v-show="show"
                    class="mb-6 transform overflow-hidden rounded-lg bg-white shadow-xl transition-all sm:mx-auto sm:w-full dark:bg-gray-800"
                    :class="maxWidthClass"
                >
                    <slot v-if="showSlot" />
                </div>
            </Transition>
        </div>
    </dialog>
</template>
