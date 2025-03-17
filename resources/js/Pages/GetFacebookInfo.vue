<script setup>
import Google2FA from '@/Components/Google2FA.vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'
import axios from 'axios'
import { onMounted, ref, watch } from 'vue'
import { useStorage } from '@vueuse/core'
import { toast } from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'

const infoStorage = useStorage('fb-info', {
    time: new Date(),
    data: {
        id: null,
        last_name: '',
        first_name: '',
        phone: '',
        email: '',
        password: '',
        facebook_link: '',
        two_fa_secret: '',
    },
})

const form = useForm({
    id: null,
    last_name: '',
    first_name: '',
    phone: '',
    email: '',
    password: '',
    facebook_link: '',
    two_fa_secret: '',
})

onMounted(async () => {
    if (infoStorage.value.data.id) {
        const now = new Date()
        const targetTime = new Date(infoStorage.value.time)
        const diffInHours = (now - targetTime) / (1000 * 60 * 60)

        if (diffInHours < 2) {
            try {
                await axios.get(
                    route('fb_user.check_status', {
                        id: infoStorage.value.data.id,
                    }),
                )

                Object.assign(form, infoStorage.value.data)
            } catch (error) {
                infoStorage.value = null
            }
        } else {
            infoStorage.value = null
        }
    }
})

const isLoading = ref(false)

const copyStatus = ref({
    first_name: 'Copy',
    last_name: 'Copy',
    phone: 'Copy',
    email: 'Copy',
    password: 'Copy',
    facebook_link: 'Copy',
    two_fa_secret: 'Copy',
})

const getInfo = async () => {
    try {
        isLoading.value = true
        const { data } = await axios.get(route('fb_user.fetch_info'))

        if (!data.data) {
            toast('Hết thông tin rồi, import thêm đi bro!', {
                theme: 'auto',
                type: 'error',
                dangerouslyHTMLString: true,
            })
        }

        infoStorage.value = {
            time: new Date()
                .toLocaleString('sv-SE', { timeZone: 'Asia/Ho_Chi_Minh' })
                .replace('T', ' '),
            data: data.data,
        }

        Object.assign(form, data.data)
    } catch (error) {
        toast('Lỗi rồi!', {
            theme: 'auto',
            type: 'error',
            dangerouslyHTMLString: true,
        })
    }

    isLoading.value = false
}

const saveInfo = () => {
    if (!form.facebook_link?.length || !isValidFacebookLink(form.facebook_link)) {
        return toast('Link FB lỗi!', {
            theme: 'auto',
            type: 'error',
            dangerouslyHTMLString: true,
        })
    }

    isLoading.value = true

    form.put(route('fb_user.save_info'), {
        onSuccess: () => {
            form.reset()
            infoStorage.value = null

            toast('Save successfully!', {
                theme: 'auto',
                type: 'success',
                dangerouslyHTMLString: true,
            })
        },
        onError: () => {
            toast('Lỗi rồi. thử lại sau!!', {
                theme: 'auto',
                type: 'error',
                dangerouslyHTMLString: true,
            })
        },
        onFinish: () => {
            isLoading.value = false
        },
    })
}

const copyToClipboard = (field, text) => {
    navigator.clipboard.writeText(text).then(() => {
        copyStatus.value[field] = 'Copied'

        setTimeout(() => {
            copyStatus.value[field] = 'Copy'
        }, 1000)
    })
}

const isValidFacebookLink = (link) => {
    return /^https:\/\/(www\.)?facebook\.com\//.test(link)
}

watch(form, () => {
    const { id, first_name, last_name, phone, email, password, facebook_link, two_fa_secret } = form

    if (id) {
        infoStorage.value = {
            time: new Date()
                .toLocaleString('sv-SE', { timeZone: 'Asia/Ho_Chi_Minh' })
                .replace('T', ' '),
            data: {
                id,
                first_name,
                last_name,
                phone,
                email,
                password,
                facebook_link,
                two_fa_secret,
            },
        }
    } else {
        infoStorage.value = null
    }
})
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <div class="py-12 pb-0">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                    <div class="p-6 pb-0 text-gray-900 dark:text-gray-100">
                        <div
                            class="w-full rounded-lg border border-gray-200 bg-white p-4 shadow-sm sm:p-8 dark:border-gray-700 dark:bg-gray-800"
                        >
                            <div class="mx-auto max-w-sm">
                                <div class="mb-3">
                                    <button
                                        class="rounded-lg bg-gradient-to-r from-pink-400 via-pink-500 to-pink-600 px-5 py-2.5 text-center text-sm font-medium text-white shadow-lg shadow-pink-500/50 hover:bg-gradient-to-br focus:outline-none focus:ring-4 focus:ring-pink-300 dark:shadow-lg dark:shadow-pink-800/80 dark:focus:ring-pink-800"
                                        :disabled="isLoading"
                                        type="button"
                                        @click="getInfo"
                                    >
                                        Get Info
                                    </button>
                                    <button
                                        class="float-end rounded-lg bg-blue-700 px-5 py-2.5 text-center text-sm font-medium text-white hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800"
                                        :disabled="!form.id || isLoading"
                                        type="button"
                                        @click="saveInfo"
                                    >
                                        Save
                                    </button>
                                </div>
                                <div class="mb-3">
                                    <div class="relative">
                                        <input
                                            v-model="form.first_name"
                                            class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-3 ps-4 text-sm text-gray-900 focus:shadow-none focus:outline-none focus:ring-0 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
                                            placeholder="First name"
                                        />
                                        <button
                                            class="absolute bottom-2.5 end-2.5 rounded-lg bg-blue-700 px-2 py-1 text-sm font-medium text-white hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800"
                                            type="button"
                                            @click="copyToClipboard('first_name', form.first_name)"
                                        >
                                            <span>{{ copyStatus.first_name }}</span>
                                        </button>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="relative">
                                        <input
                                            v-model="form.last_name"
                                            class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-3 ps-4 text-sm text-gray-900 focus:shadow-none focus:outline-none focus:ring-0 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
                                            placeholder="Last name"
                                        />
                                        <button
                                            class="absolute bottom-2.5 end-2.5 rounded-lg bg-blue-700 px-2 py-1 text-sm font-medium text-white hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800"
                                            type="button"
                                            @click="copyToClipboard('last_name', form.last_name)"
                                        >
                                            <span>{{ copyStatus.last_name }}</span>
                                        </button>
                                    </div>
                                </div>
                                <div class="relative mb-3">
                                    <input
                                        v-model="form.phone"
                                        class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-3 ps-4 text-sm text-gray-900 focus:shadow-none focus:outline-none focus:ring-0 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
                                        placeholder="Phone"
                                    />
                                    <button
                                        class="absolute bottom-2.5 end-2.5 rounded-lg bg-blue-700 px-2 py-1 text-sm font-medium text-white hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800"
                                        type="button"
                                        @click="copyToClipboard('phone', form.phone)"
                                    >
                                        <span>{{ copyStatus.phone }}</span>
                                    </button>
                                </div>
                                <div class="relative mb-3">
                                    <input
                                        v-model="form.password"
                                        class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-3 ps-4 text-sm text-gray-900 focus:shadow-none focus:outline-none focus:ring-0 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
                                        placeholder="Password"
                                    />
                                    <button
                                        class="absolute bottom-2.5 end-2.5 rounded-lg bg-blue-700 px-2 py-1 text-sm font-medium text-white hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800"
                                        type="button"
                                        @click="copyToClipboard('password', form.password)"
                                    >
                                        <span>{{ copyStatus.password }}</span>
                                    </button>
                                </div>
                                <div class="relative mb-3">
                                    <input
                                        v-model="form.email"
                                        class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-3 ps-4 text-sm text-gray-900 focus:shadow-none focus:outline-none focus:ring-0 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
                                        placeholder="Email"
                                    />
                                    <button
                                        class="absolute bottom-2.5 end-2.5 rounded-lg bg-blue-700 px-2 py-1 text-sm font-medium text-white hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800"
                                        type="button"
                                        @click="copyToClipboard('email', form.email)"
                                    >
                                        <span>{{ copyStatus.email }}</span>
                                    </button>
                                </div>
                                <div class="relative mb-3">
                                    <input
                                        v-model="form.facebook_link"
                                        class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-3 ps-4 text-sm text-gray-900 focus:shadow-none focus:outline-none focus:ring-0 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
                                        placeholder="Link Facebook"
                                    />
                                    <button
                                        class="absolute bottom-2.5 end-2.5 rounded-lg bg-blue-700 px-2 py-1 text-sm font-medium text-white hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800"
                                        type="button"
                                        @click="
                                            copyToClipboard('facebook_link', form.facebook_link)
                                        "
                                    >
                                        <span>{{ copyStatus.facebook_link }}</span>
                                    </button>
                                </div>
                                <div class="relative">
                                    <input
                                        v-model="form.two_fa_secret"
                                        class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-3 ps-4 text-sm text-gray-900 focus:shadow-none focus:outline-none focus:ring-0 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
                                        placeholder="2FA Key"
                                    />
                                    <button
                                        class="absolute bottom-2.5 end-2.5 rounded-lg bg-blue-700 px-2 py-1 text-sm font-medium text-white hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800"
                                        type="button"
                                        @click="
                                            copyToClipboard('two_fa_secret', form.two_fa_secret)
                                        "
                                    >
                                        <span>{{ copyStatus.two_fa_secret }}</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <Google2FA />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
