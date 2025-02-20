<script setup>
import Modal from '@/Components/Modal.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import SvgLoading from '@/Components/SvgLoading.vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'
import axios from 'axios'
import { onMounted, ref, watch } from 'vue'

const axiosIns = axios.create({
    baseURL: 'https://api.dongvanfb.net/',
})

axiosIns.interceptors.request.use(
    (config) => {
        isLoading.value = true
        return config
    },
    (error) => {
        isLoading.value = false
        return Promise.reject(error)
    },
)

axiosIns.interceptors.response.use(
    (response) => {
        isLoading.value = false
        return response
    },
    (error) => {
        isLoading.value = false
        return Promise.reject(error)
    },
)

const EMPTY = 0
const SUCCESS = 1
const ERROR = 2

const props = defineProps({
    api_key: {
        type: String,
        required: true,
    },
})

const form = useForm({
    api_key: props.api_key,
})

const modalValue = ref(false)
const modalRef = ref()
const listMail = ref([])
const accountTypes = ref([])
const accountType = ref(null)
const apiKeyStatus = ref(EMPTY)
const isKeyChange = ref(false)
const isLoading = ref(false)

onMounted(async () => {
    if (form.api_key.length > 0) {
        apiKeyStatus.value = (await checkBalance()) ? SUCCESS : ERROR
    }

    // fetchAccountType()
})

const checkBalance = async () => {
    try {
        const { data } = await axiosIns.get('user/balance?apikey=' + form.api_key)

        return data.status
    } catch (error) {
        return false
    }
}

const applyKey = async () => {
    const status = await checkBalance()

    if (status) {
        form.post(route('apply_key'), {
            onSuccess: () => {
                apiKeyStatus.value = SUCCESS
                isKeyChange.value = false
            },
        })
    } else {
        apiKeyStatus.value = ERROR
    }
}

// const fetchAccountType = async () => {
//     try {
//         const { data } = await axiosIns.get('user/account_type?apikey=' + form.api_key)

//         if (data.status) {
//             accountTypes.value = data.data.filter((item) => item.quality > 0 && item.price < 100)
//         }
//     } catch (error) {
//         accountTypes.value = []
//     }
// }

const fetchAccountType = async () => {
    try {
        const { data } = await axiosIns.get('user/account_type?apikey=' + form.api_key)

        if (!data.status) {
            throw new Error('')
        }

        const firstItem = data.data.find((item) => item.price === 50 && item.quality > 0)

        if (firstItem) {
            return firstItem.id
        } else {
            throw new Error('')
        }
    } catch (err) {
        throw new Error('')
    }
}

const buyMail = async () => {
    try {
        const account_type = await fetchAccountType()

        const { data } = await axiosIns.get('user/buy', {
            params: {
                apikey: form.api_key,
                account_type: account_type,
                quality: 1,
                type: 'full',
            },
        })

        if (!data.status) {
            modalValue.value = true
            // accountType.value = null
            return
        }

        const [mail, pass, refresh_token, client_id] = data.data.list_data[0].split('|')

        const result = {
            mail,
            pass,
            refresh_token,
            client_id,
            code: null,
            code_copied: false,
            mail_copied: false,
        }

        listMail.value.unshift(result)
    } catch (error) {
        modalValue.value = true
        // accountType.value = null
    }
}

const copyMail = async (index, onlyMail = false) => {
    try {
        const item = listMail.value[index]
        await navigator.clipboard.writeText(item.mail + (onlyMail ? '' : '|' + item.pass))
        item.mail_copied = true
        setTimeout(() => {
            item.mail_copied = false
        }, 1000)
    } catch (err) {
        console.log(err)
    }
}

const copyCode = async (index) => {
    try {
        const item = listMail.value[index]
        await navigator.clipboard.writeText(item.code)
        item.code_copied = true
        setTimeout(() => {
            item.code_copied = false
        }, 1000)
    } catch (err) {
        console.log(err)
    }
}

const fetchFacebookCode = async (index) => {
    try {
        const item = listMail.value[index]

        const { data } = await axiosIns.post('https://tools.dongvanfb.net/api/get_code_oauth2', {
            email: item.mail,
            refresh_token: item.refresh_token,
            client_id: item.client_id,
            type: 'facebook',
        })

        if (data.status) {
            item.code = data.code
        }
    } catch (error) {
        console.log(error)
    }
}

const deleteMail = (mail) => {
    listMail.value = listMail.value.filter((item) => item.mail !== mail)
}

watch(
    () => form.api_key,
    (v) => {
        if (v != props.api_key) {
            isKeyChange.value = true
        } else {
            isKeyChange.value = false

            if (props.api_key.length) {
                apiKeyStatus.value = SUCCESS
            }
        }
        isKeyChange.value = v != props.api_key
    },
)
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                            <div class="w-100 mb-3 md:w-1/2">
                                <div class="relative">
                                    <input
                                        v-model="form.api_key"
                                        class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-3 ps-4 text-sm text-gray-900 focus:shadow-none focus:outline-none focus:ring-0 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
                                        :class="{
                                            'border-green-500 focus:border-green-500 dark:border-green-500 focus:dark:border-green-500':
                                                apiKeyStatus == SUCCESS && !isKeyChange,
                                            'border-red-500 dark:border-red-500':
                                                apiKeyStatus == ERROR,
                                        }"
                                        placeholder="API Key"
                                    />
                                    <button
                                        class="absolute bottom-2.5 end-2.5 rounded-lg bg-blue-700 px-4 py-1 text-sm font-medium text-white hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800"
                                        :class="{
                                            'cursor-not-allowed opacity-50':
                                                !isKeyChange || isLoading,
                                        }"
                                        :disabled="!isKeyChange || isLoading"
                                        type="button"
                                        @click="applyKey"
                                    >
                                        <SvgLoading v-show="isLoading" />
                                        <span v-show="!isLoading">Apply</span>
                                    </button>
                                </div>
                            </div>
                            <div>
                                <!-- <select
                                    v-model="accountType"
                                    class="inline-block w-2/3 rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 md:w-1/3 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                                    :disabled="isLoading"
                                >
                                    <option :value="null">Chọn loại Mail</option>
                                    <option
                                        v-for="item in accountTypes"
                                        :key="item.id"
                                        :value="item.id"
                                    >
                                        {{ item.name }} - {{ item.price }}đ - còn:
                                        {{ item.quality }}
                                    </option>
                                </select>

                                <button
                                    class="mb-2 ml-2 rounded-lg bg-teal-700 px-3 py-2.5 text-sm font-medium text-white hover:bg-teal-800 focus:outline-none focus:ring-4 focus:ring-teal-300 dark:bg-teal-600 dark:hover:bg-teal-700 dark:focus:ring-teal-800"
                                    :class="{
                                        'cursor-not-allowed opacity-50':
                                            apiKeyStatus != SUCCESS || isLoading,
                                    }"
                                    :disabled="apiKeyStatus != SUCCESS || isLoading"
                                    type="button"
                                    @click="fetchAccountType"
                                >
                                    <SvgLoading v-show="isLoading" />
                                    <svg
                                        v-show="!isLoading"
                                        aria-hidden="true"
                                        class="inline h-4 w-4 text-white"
                                        fill="none"
                                        height="24"
                                        viewBox="0 0 24 24"
                                        width="24"
                                        xmlns="http://www.w3.org/2000/svg"
                                    >
                                        <path
                                            d="M17.651 7.65a7.131 7.131 0 0 0-12.68 3.15M18.001 4v4h-4m-7.652 8.35a7.13 7.13 0 0 0 12.68-3.15M6 20v-4h4"
                                            stroke="currentColor"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                        />
                                    </svg>
                                </button> -->
                                <button
                                    class="mb-2 me-2 ml-2 rounded-lg bg-green-700 px-5 py-3 text-sm font-medium text-white hover:bg-green-800 focus:outline-none focus:ring-4 focus:ring-green-300 dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-green-800"
                                    :class="{
                                        'cursor-not-allowed opacity-50':
                                            apiKeyStatus != SUCCESS || isLoading,
                                    }"
                                    :disabled="apiKeyStatus != SUCCESS || isLoading"
                                    type="button"
                                    @click="buyMail"
                                >
                                    <SvgLoading v-show="isLoading" />
                                    <span v-show="!isLoading">Mua Mail</span>
                                </button>
                            </div>

                            <table
                                class="w-full text-center text-sm text-xs text-gray-500 rtl:text-right dark:text-gray-400"
                            >
                                <thead
                                    class="bg-gray-50 text-xs uppercase text-gray-700 dark:bg-gray-700 dark:text-gray-400"
                                >
                                    <tr>
                                        <th
                                            class="px-6 py-3"
                                            scope="col"
                                        >
                                            Email
                                        </th>
                                        <th
                                            class="px-6 py-3"
                                            scope="col"
                                        >
                                            Facebook Code
                                        </th>
                                        <th
                                            class="px-6 py-3"
                                            scope="col"
                                        >
                                            Mail|Password
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="(item, index) in listMail"
                                        :key="index"
                                        class="border-b border-gray-200 bg-white hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-600"
                                    >
                                        <th
                                            class="whitespace-nowrap px-3 py-4 font-medium text-gray-900 dark:text-white"
                                            scope="row"
                                        >
                                            <p class="mb-1">{{ item.mail }}</p>
                                            <button
                                                class="mb-2 me-2 rounded-lg bg-blue-700 px-3 py-1.5 text-sm text-xs font-medium text-white hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800"
                                                :class="{
                                                    'cursor-not-allowed opacity-50': isLoading,
                                                }"
                                                :disabled="isLoading"
                                                @click="copyMail(index, true)"
                                            >
                                                <span v-if="!item.mail_copied">Copy</span>
                                                <span v-else>Copied!</span>
                                            </button>
                                        </th>
                                        <td class="px-3 py-4">
                                            <p class="mb-1">{{ item.code ?? 'Chưa có' }}</p>
                                            <button
                                                class="mb-2 me-2 rounded-lg bg-teal-700 px-3 py-1.5 text-sm text-xs font-medium text-white hover:bg-teal-800 focus:outline-none focus:ring-4 focus:ring-teal-300 dark:bg-teal-600 dark:hover:bg-teal-700 dark:focus:ring-teal-800"
                                                @click="fetchFacebookCode(index)"
                                            >
                                                <svg
                                                    v-show="!isLoading"
                                                    aria-hidden="true"
                                                    class="inline h-4 w-4 text-white"
                                                    fill="none"
                                                    height="24"
                                                    viewBox="0 0 24 24"
                                                    width="24"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                >
                                                    <path
                                                        d="M17.651 7.65a7.131 7.131 0 0 0-12.68 3.15M18.001 4v4h-4m-7.652 8.35a7.13 7.13 0 0 0 12.68-3.15M6 20v-4h4"
                                                        stroke="currentColor"
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                    />
                                                </svg>
                                                <SvgLoading v-show="isLoading" />
                                            </button>
                                            <button
                                                class="mb-2 me-2 rounded-lg bg-blue-700 px-3 py-1.5 text-sm text-xs font-medium text-white hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800"
                                                :class="{
                                                    'cursor-not-allowed opacity-50': !item.code,
                                                }"
                                                :disabled="!item.code"
                                                @click="copyCode(index)"
                                            >
                                                <span v-if="!item.code_copied">Copy</span>
                                                <span v-else>Copied!</span>
                                            </button>
                                        </td>
                                        <td class="px-2 py-4 text-right">
                                            <button
                                                class="mb-2 me-2 rounded-lg bg-blue-700 px-3 py-1.5 text-sm text-xs font-medium text-white hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800"
                                                :class="{
                                                    'cursor-not-allowed opacity-50': isLoading,
                                                }"
                                                :disabled="isLoading"
                                                @click="copyMail(index)"
                                            >
                                                <span v-if="!item.mail_copied">Copy</span>
                                                <span v-else>Copied!</span>
                                            </button>
                                            <button
                                                class="mb-2 me-2 rounded-lg bg-red-700 px-3 py-1.5 text-sm text-xs font-medium text-white hover:bg-red-800 focus:outline-none focus:ring-4 focus:ring-red-300 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-800"
                                                :class="{
                                                    'cursor-not-allowed opacity-50': isLoading,
                                                }"
                                                :disabled="isLoading"
                                                @click="deleteMail(item.mail)"
                                            >
                                                <span>X</span>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <Modal
            ref="modalRef"
            :show="modalValue"
            @close="
                () => {
                    modalValue = false
                    // fetchAccountType()
                }
            "
        >
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    Hết mail rồi cha ơi!
                </h2>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="modalRef.close()">OK</SecondaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
