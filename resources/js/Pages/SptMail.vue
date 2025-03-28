<script setup>
import Google2FA from '@/Components/Google2FA.vue'
import Modal from '@/Components/Modal.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import SvgLoading from '@/Components/SvgLoading.vue'
import { useAxios } from '@/Composables/axiosIns'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { computed, onMounted, ref, watch } from 'vue'

const { axiosIns, requestLoading } = useAxios('https://api.sptmail.com/')

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
const listMail = ref([
    // {
    //     mail: 'mail',
    //     pass: null,
    //     refresh_token: null,
    //     client_id: null,
    //     code: null,
    //     code_copied: false,
    //     mail_copied: false,
    // },
])
const myPrice = ref(0)
const apiKeyStatus = ref(EMPTY)
const isKeyChange = ref(false)
const isloadingBuying = ref(false)

const isLoading = computed(() => {
    return isloadingBuying.value || requestLoading.value
})

onMounted(async () => {
    if (form.api_key.length > 0) {
        apiKeyStatus.value = (await checkBalance()) ? SUCCESS : ERROR
    }
})

const priceFormat = computed(() => {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND',
    }).format(myPrice.value)
})

const checkBalance = async () => {
    try {
        const { data } = await axiosIns.get('api/auth/profile?apiKey=' + form.api_key)

        myPrice.value = data.user.balanceInWallet ?? 0

        return data.success
    } catch (error) {
        myPrice.value = 0
        return false
    }
}

const applyKey = async () => {
    const status = await checkBalance()

    if (status) {
        form.post(route('apply_key', { page: 'sptmail' }), {
            onSuccess: () => {
                apiKeyStatus.value = SUCCESS
                isKeyChange.value = false
            },
        })
    } else {
        apiKeyStatus.value = ERROR
    }
}

const worker = new Worker('/js/workers/worker.js', { type: 'module' })

worker.onmessage = (event) => {
    listMail.value.unshift({
        mail: event.data,
        pass: null,
        refresh_token: null,
        client_id: null,
        code: null,
        code_copied: false,
        mail_copied: false,
    })
    isloadingBuying.value = false
}

const buyMail = () => {
    isloadingBuying.value = true
    worker.postMessage({ apiKey: form.api_key })
}

// const buyMail = async () => {
//     let isSuccess = false
//     isloadingBuying.value = true

//     while (!isSuccess) {
//         try {
//             const { data } = await axiosIns.get('api/otp-services/mail-otp-rental', {
//                 params: {
//                     apiKey: form.api_key,
//                     otpServiceCode: 'facebook',
//                 },
//             })

//             if (data.success) {
//                 const mail = data.gmail

//                 const result = {
//                     mail,
//                     pass: null,
//                     refresh_token: null,
//                     client_id: null,
//                     code: null,
//                     code_copied: false,
//                     mail_copied: false,
//                 }

//                 listMail.value.unshift(result)
//                 checkBalance()
//                 isSuccess = true // Thoát vòng lặp khi thành công
//             }
//         } catch (error) {
//             console.error('Request failed, retrying...', error)
//         }

//         await new Promise((resolve) => setTimeout(resolve, 1000)) // Delay 1 giây trước khi thử lại
//     }

//     isloadingBuying.value = false
// }

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

        const { data } = await axiosIns.get('api/otp-services/mail-otp-lookup', {
            params: { apiKey: form.api_key, otpServiceCode: 'facebook', gmail: item.mail },
        })

        if (data.status == 'SUCCESS') {
            item.code = data.otp
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
    <Head title="SPT Mail" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="inline-block text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200"
            >
                SPT Mail
            </h2>
            <h2
                class="text-md ml-auto inline-block font-semibold leading-tight text-green-800 dark:text-green-200"
            >
                {{ priceFormat }}
            </h2>
        </template>

        <div class="pt-[3rem]">
            <div class="mx-auto min-h-[350px] max-w-7xl sm:px-6 lg:px-8">
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
                                            class="break-all px-6 py-3"
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
                                            class="whitespace-nowrap px-1 py-4 font-medium text-gray-900 md:px-3 dark:text-white"
                                            scope="row"
                                        >
                                            <div class="mb-1 w-[100px] md:w-auto">
                                                <p class="truncate">
                                                    {{ item.mail }}
                                                </p>
                                            </div>
                                            <button
                                                class="mb-2 me-2 rounded-lg bg-blue-700 px-3 py-1.5 text-sm text-xs font-medium text-white hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800"
                                                :class="{
                                                    'cursor-not-allowed opacity-50': isLoading,
                                                }"
                                                :disabled="isLoading"
                                                @click="copyMail(index, true)"
                                            >
                                                <span v-if="!item.mail_copied">Copy 1</span>
                                                <span v-else>Copied!</span>
                                            </button>
                                        </th>
                                        <td class="px-1 py-4">
                                            <p class="mb-1">{{ item.code ?? 'Chưa có' }}</p>
                                            <button
                                                class="mb-2 me-2 rounded-lg bg-teal-700 px-3 py-1.5 text-sm text-xs font-medium text-white hover:bg-teal-800 focus:outline-none focus:ring-4 focus:ring-teal-300 dark:bg-teal-600 dark:hover:bg-teal-700 dark:focus:ring-teal-800"
                                                @click="fetchFacebookCode(index)"
                                            >
                                                <span v-show="!isLoading">Check</span>
                                                <SvgLoading v-show="isLoading" />
                                            </button>
                                            <button
                                                class="mb-2 me-2 rounded-lg bg-blue-700 px-3 py-1.5 text-sm text-xs font-medium text-white hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800"
                                                :class="{
                                                    'cursor-not-allowed opacity-50': !item.code,
                                                }"
                                                :disabled="!item.code"
                                                @click="copyCode(index, true)"
                                            >
                                                <span v-if="!item.code_copied">Copy 2</span>
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
                                                <span v-if="!item.mail_copied">Copy 3</span>
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
                                    <tr
                                        v-if="!listMail.length"
                                        class="border-b border-gray-200 bg-white hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-600"
                                    >
                                        <td
                                            class="px-1 py-4"
                                            colspan="3"
                                        >
                                            Có cái nịt
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <Google2FA />
        </div>
        <Modal
            ref="modalRef"
            :show="modalValue"
            @close="modalValue = false"
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
