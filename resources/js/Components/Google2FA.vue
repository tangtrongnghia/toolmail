<script setup>
import { useIntervalFn } from '@vueuse/core'
import axios from 'axios'
import { ref, watch } from 'vue'

const secretKey = ref('')
const code = ref('')
const isCopied = ref(false)
const seconds = ref(5000)

const fetchData = async () => {
    try {
        seconds.value = 5000

        if (!secretKey.value.length) {
            return
        }

        const response = await axios.get(route('google_2fa', { secretKey: secretKey.value }))

        code.value = String(response.data)
    } catch (error) {
        console.error('Lỗi khi gọi API:', error)
    }
}

const { pause, resume } = useIntervalFn(fetchData, seconds)

const copyCode = async () => {
    try {
        await navigator.clipboard.writeText(code.value)
        isCopied.value = true
        setTimeout(() => {
            isCopied.value = false
        }, 1000)
    } catch (err) {
        console.log(err)
    }
}

watch(secretKey, (v) => {
    if (v.length) {
        seconds.value = 1000
        resume()
    } else {
        pause()
        code.value = ''
    }
})
</script>

<template>
    <div class="py-1">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                <div class="px-6 py-3 text-gray-900 dark:text-gray-100">
                    <div
                        class="mb-4 w-full rounded-lg border border-gray-200 bg-gray-50 dark:border-gray-600 dark:bg-gray-700"
                    >
                        <input
                            v-model="secretKey"
                            class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-3 ps-4 text-sm text-gray-900 focus:shadow-none focus:outline-none focus:ring-0 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
                            placeholder="2FA Key"
                            @input="secretKey = secretKey.replace(/[^a-zA-Z0-9]/g, '')"
                        />
                    </div>

                    <div>
                        <h2
                            class="mb-2 inline-block text-lg font-semibold text-gray-900 dark:text-white"
                        >
                            Kết quả: {{ code }}
                        </h2>
                        <button
                            class="float-end mb-2 me-2 rounded-lg bg-blue-700 px-3 py-1.5 text-sm text-xs font-medium text-white hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800"
                            :class="{
                                'cursor-not-allowed opacity-50': !code?.length,
                            }"
                            :disabled="!code.length"
                            @click="copyCode"
                        >
                            <span v-if="!isCopied">Copy</span>
                            <span v-else>Copied!</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
