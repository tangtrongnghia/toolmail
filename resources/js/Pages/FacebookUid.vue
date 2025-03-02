<script setup>
import SvgLoading from '@/Components/SvgLoading.vue'
import { useAxios } from '@/Composables/axiosIns'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head } from '@inertiajs/vue3'
import { ref } from 'vue'
const { axiosIns, requestLoading: isLoading } = useAxios()

const input = ref('')
const output = ref([])
const is_copied = ref(false)
const fileInput = ref(null)

const getUid = async () => {
    try {
        const facebookLinks = input.value.split('\n').map((link) => link.trim())
        const response = await axiosIns.post('/facebook-uid', {
            input: facebookLinks,
        })

        output.value = response.data
    } catch (error) {
        alert('Lỗi rồi')
    }
}

const copyToClipboard = () => {
    const uid = output.value.map((uid) => uid ?? 'Error')
    const text = uid.join('\n')
    navigator.clipboard.writeText(text)
    is_copied.value = true

    setTimeout(() => {
        is_copied.value = false
    }, 1000)
}

const openFilePicker = () => {
    fileInput.value.click()
}

const handleFileUpload = (event) => {
    const file = event.target.files[0]
    if (!file) return

    const reader = new FileReader()
    reader.onload = (e) => {
        const text = e.target.result
        const urls = text
            .split(/\r?\n/)
            .map((url) => url.trim())
            .filter(Boolean)

        input.value = urls.join('\n')
    }

    reader.readAsText(file)
}
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="inline-block text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200"
            >
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div
                            class="w-full rounded-lg border border-gray-200 bg-white p-4 shadow-sm sm:p-8 dark:border-gray-700 dark:bg-gray-800"
                        >
                            <div
                                class="mb-4 w-full rounded-lg border border-gray-200 bg-gray-50 dark:border-gray-600 dark:bg-gray-700"
                            >
                                <div class="rounded-t-lg bg-white px-4 py-2 dark:bg-gray-800">
                                    <textarea
                                        id="comment"
                                        v-model.trim="input"
                                        class="w-full border-0 bg-white px-0 text-sm text-gray-900 focus:ring-0 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400"
                                        placeholder="Facebook profile share links..."
                                        required
                                        rows="4"
                                    />
                                </div>
                                <div
                                    class="flex items-center justify-between border-t border-gray-200 px-3 py-2 dark:border-gray-600"
                                >
                                    <button
                                        class="inline-flex items-center rounded-lg bg-blue-700 px-4 py-2.5 text-center text-xs font-medium text-white hover:bg-blue-800 focus:ring-4 focus:ring-blue-200 dark:focus:ring-blue-900"
                                        :class="{
                                            'cursor-not-allowed opacity-50': isLoading,
                                        }"
                                        :disabled="isLoading"
                                        type="button"
                                        @click="getUid"
                                    >
                                        <SvgLoading v-show="isLoading" />
                                        <span :class="{ 'ms-1': isLoading }">
                                            Get UID{{ isLoading ? '...' : '' }}
                                        </span>
                                    </button>
                                    <div class="flex space-x-1 ps-0 sm:ps-2 rtl:space-x-reverse">
                                        <input
                                            ref="fileInput"
                                            accept=".txt"
                                            class="hidden"
                                            type="file"
                                            @change="handleFileUpload"
                                        />
                                        <button
                                            class="inline-flex cursor-pointer items-center justify-center rounded-sm p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-600 dark:hover:text-white"
                                            type="button"
                                            @click="openFilePicker"
                                        >
                                            <svg
                                                aria-hidden="true"
                                                class="h-4 w-4"
                                                fill="none"
                                                viewBox="0 0 12 20"
                                                xmlns="http://www.w3.org/2000/svg"
                                            >
                                                <path
                                                    d="M1 6v8a5 5 0 1 0 10 0V4.5a3.5 3.5 0 1 0-7 0V13a2 2 0 0 0 4 0V6"
                                                    stroke="currentColor"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <h2
                                    class="mb-2 inline-block text-lg font-semibold text-gray-900 dark:text-white"
                                >
                                    Kết quả:
                                </h2>
                                <button
                                    class="float-end mb-2 me-2 rounded-lg bg-blue-700 px-3 py-1.5 text-sm text-xs font-medium text-white hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800"
                                    @click="copyToClipboard"
                                >
                                    <span v-if="!is_copied">Copy</span>
                                    <span v-else>Copied!</span>
                                </button>
                            </div>

                            <ul
                                class="max-w-md list-inside space-y-1 text-gray-500 dark:text-gray-400"
                            >
                                <li
                                    v-for="uid in output"
                                    :key="uid"
                                    class="flex items-center"
                                >
                                    <svg
                                        aria-hidden="true"
                                        class="me-2 h-3.5 w-3.5 shrink-0"
                                        :class="{
                                            'text-green-500 dark:text-green-400': uid,
                                            'text-red-500 dark:text-red-400': !uid,
                                        }"
                                        fill="currentColor"
                                        viewBox="0 0 20 20"
                                        xmlns="http://www.w3.org/2000/svg"
                                    >
                                        <path
                                            d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z"
                                        />
                                    </svg>
                                    {{ uid ?? 'Error' }}
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
