<script setup>
import SvgLoading from '@/Components/SvgLoading.vue'
import { useAxios } from '@/Composables/axiosIns'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { onMounted, ref } from 'vue'
const { axiosIns, requestLoading: isLoading } = useAxios()

const props = defineProps({
    dongvanfb_key: {
        type: String,
        required: true,
    },
    unlimitmail_key: {
        type: String,
        required: true,
    },
    muamail_key: {
        type: String,
        required: true,
    },
})

const form = useForm({
    api_key: '',
})

const vendors = ref([
    {
        name: 'Dongvanfb',
        logo: 'https://dongvanfb.net/_nuxt/logo.76d23961.png',
        route: 'dongvanfb',
        price: 0,
        status: false,
    },
    {
        name: 'Unlimitmail',
        logo: 'https://unlimitmail.com/build/images/logounlimit2.png',
        route: 'unlimitmail',
        price: 0,
        status: false,
    },
    {
        name: 'MuamailStore',
        logo: '/assets/images/muamailstore.png',
        route: 'muamail',
        price: 0,
        status: false,
    },
])

const formInput = ref({
    dongvanfb_key: props.dongvanfb_key,
    unlimitmail_key: props.unlimitmail_key,
    muamail_key: props.muamail_key,
})

onMounted(() => {
    checkBalanceMuamailstore()
    checkBalanceUnlimitmail()
    checkBalanceDongvanfb()
})

const applyKey = async (routeName) => {
    let result = false

    switch (routeName) {
        case 'dongvanfb':
            result = await checkBalanceDongvanfb()
            break
        case 'unlimitmail':
            result = await checkBalanceUnlimitmail()
            break
        case 'muamail':
            result = await checkBalanceMuamailstore()
            break
    }

    if (result) {
        form.api_key = formInput.value[routeName + '_key']

        console.log(form)
        form.post(route('apply_key', { page: routeName }))
    }
}

const checkBalanceDongvanfb = async () => {
    const vendor = vendors.value.find((v) => v.route === 'dongvanfb')

    try {
        const { data } = await axiosIns.get(
            'https://api.dongvanfb.net/user/balance?apikey=' + formInput.value.dongvanfb_key,
        )

        vendor.price = data.balance ?? 0
        vendor.status = data.status

        return data.status
    } catch (error) {
        vendor.price = 0
        return false
    }
}

const checkBalanceUnlimitmail = async () => {
    const vendor = vendors.value.find((v) => v.route === 'unlimitmail')

    try {
        const { data } = await axiosIns.get(
            'https://unlimitmail.com/api/user?token=' + formInput.value.unlimitmail_key,
        )

        vendor.price = data.user.coin ?? 0
        vendor.status = data.success

        return data.success
    } catch (error) {
        vendor.price = 0
        return false
    }
}

const checkBalanceMuamailstore = async () => {
    const vendor = vendors.value.find((v) => v.route === 'muamail')

    try {
        const { data } = await axiosIns.get(
            'https://api.muamail.store/users/balance/?api_key=' + formInput.value.muamail_key,
        )

        vendor.price = data.data ?? 0 // Thay đổi giá trị price theo mong muốn
        vendor.status = data.is_success

        return data.is_success
    } catch (error) {
        vendor.price = 0
        return false
    }
}

const formatPrice = (price) => {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND',
    }).format(price)
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
                            <div class="mb-4 flex items-center justify-between">
                                <h5
                                    class="text-xl font-bold leading-none text-gray-900 dark:text-white"
                                >
                                    Danh sách
                                </h5>
                            </div>
                            <div class="flow-root">
                                <ul
                                    class="divide-y divide-gray-200 dark:divide-gray-700"
                                    role="list"
                                >
                                    <li
                                        v-for="vendor in vendors"
                                        :key="vendor.name"
                                        class="py-3 sm:py-4"
                                    >
                                        <div class="flex items-center">
                                            <a
                                                class="shrink-0"
                                                :href="route('dashboard', { page: vendor.route })"
                                            >
                                                <img
                                                    alt="Michael image"
                                                    class="h-8 w-8 rounded-full"
                                                    :src="vendor.logo"
                                                />
                                            </a>
                                            <div class="ms-4 min-w-0 flex-1">
                                                <Link
                                                    class="truncate text-sm font-medium text-gray-900 dark:text-white"
                                                    :href="
                                                        route('dashboard', { page: vendor.route })
                                                    "
                                                >
                                                    {{ vendor.name }}
                                                </Link>
                                                <span
                                                    class="float-end font-semibold leading-tight text-green-800 dark:text-green-200"
                                                >
                                                    {{ formatPrice(vendor.price) }}
                                                </span>

                                                <div class="relative mt-2 block w-full md:w-1/2">
                                                    <input
                                                        v-model="formInput[vendor.route + '_key']"
                                                        class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-3 ps-4 text-sm text-gray-900 focus:shadow-none focus:outline-none focus:ring-0 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
                                                        :class="{
                                                            'border-green-500 focus:border-green-500 dark:border-green-500 focus:dark:border-green-500':
                                                                vendor.status,
                                                            'border-red-500 dark:border-red-500':
                                                                !vendor.status,
                                                        }"
                                                        placeholder="API Key"
                                                    />
                                                    <button
                                                        class="absolute bottom-2.5 end-2.5 rounded-lg bg-blue-700 px-4 py-1 text-sm font-medium text-white hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800"
                                                        type="button"
                                                        @click="applyKey(vendor.route)"
                                                    >
                                                        <SvgLoading v-show="isLoading" />
                                                        <span v-show="!isLoading">Apply</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
