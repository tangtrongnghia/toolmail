<script setup>
import { computed, nextTick, ref, watch, watchEffect } from 'vue'
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import FacebookUserStatus from '@/Components/FacebookUserStatus.vue'
import { toast } from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'

const props = defineProps({
    facebook_data: {
        type: Object,
    },
    countPending: Number,
    countProcessing: Number,
    countSuccess: Number,
    statusFilter: String,
})

const masterStatusFilter = [
    {
        text: 'All',
        color: 'bg-pink-600',
        value: 'all',
    },
    {
        text: 'Pending',
        color: 'bg-blue-600',
        value: 'pending',
    },
    {
        text: 'In Process',
        color: 'bg-yellow-600',
        value: 'inprocess',
    },
    {
        text: 'Success',
        color: 'bg-green-600',
        value: 'success',
    },
]

const file = ref(null)
const form = useForm({ file: null })
const formDelete = useForm({ ids: null, all: false })
const checkedIds = ref([])
const checkedAll = ref(false)
const inputStatusFilter = ref('all')
const dropdownOpen = ref(false)

const currentStatusStyle = computed(() => {
    return masterStatusFilter.find((item) => item.value == inputStatusFilter.value)
})

const toggleDropdown = () => {
    dropdownOpen.value = !dropdownOpen.value
}

const selectFilter = async (newStatus) => {
    inputStatusFilter.value = newStatus
    dropdownOpen.value = false

    await router.get(route('fb_user.list', { status: newStatus }), {}, { replace: true })
    await nextTick()

    dropdownOpen.value = true
}

const toggleAll = () => {
    checkedAll.value = !checkedAll.value

    if (checkedAll.value) {
        checkedIds.value = props.facebook_data.data.map((item) => item.id) || []
    } else {
        checkedIds.value = []
    }
}

const toggle = () => {
    checkedAll.value = checkedIds.value.length == props.facebook_data.data.length
}

const deleteUser = (all = false) => {
    if (confirm('Chắc chưa!')) {
        formDelete.ids = checkedIds
        formDelete.all = all
        formDelete.delete(route('fb_user.delete'), {
            onSuccess: () => {
                formDelete.ids = null

                toast('Delete successfully!', {
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
        })
    }
}

const importCsv = () => {
    form.post(route('fb_user.import'), {
        forceFormData: true,
        onSuccess: () => {
            file.value = null

            toast('Import successfully!', {
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
    })
}

const getCount = (value = 'all') => {
    switch (value) {
        case 'pending':
            return props.countPending || 0

        case 'inprocess':
            return props.countProcessing || 0

        case 'success':
            return props.countSuccess || 0

        default:
            return props.facebook_data.total
    }
}

const exportCsv = () => {
    window.open(route('fb_user.export'), '_blank')
}

watchEffect(() => {
    inputStatusFilter.value = props.statusFilter
})

watch(inputStatusFilter, (newStatus) => {
    router.get(route('fb_user.list', { status: newStatus }), {}, { replace: true })
})
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
                                    List FB user
                                </h5>
                            </div>
                            <div class="rounded border border-gray-200 p-4 dark:border-gray-700">
                                <h2 class="mb-3 text-lg font-bold">Import Users CSV</h2>

                                <input
                                    accept=".csv,.xlsx"
                                    class="w-full rounded-lg border border-gray-300 bg-gray-50 p-1 text-gray-900 focus:outline-none md:w-1/3 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-400 dark:placeholder-gray-400"
                                    type="file"
                                    @change="(e) => (form.file = e.target.files[0])"
                                />

                                <button
                                    class="ml-1 rounded-lg bg-green-700 px-5 py-2.5 text-center text-sm font-medium text-white hover:bg-green-800 focus:ring-4 focus:ring-green-300 dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-green-800"
                                    :disabled="form.processing"
                                    @click="importCsv"
                                >
                                    Import
                                </button>
                                <button
                                    class="ml-1 rounded-lg bg-blue-700 px-5 py-2.5 text-center text-sm font-medium text-white hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800"
                                    @click="exportCsv"
                                >
                                    Export
                                </button>
                                <button
                                    class="ml-1 rounded-lg bg-red-700 px-5 py-2.5 text-center text-sm font-medium text-white hover:bg-red-800 focus:ring-4 focus:ring-red-300 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-800"
                                    :disabled="formDelete.processing || !checkedIds?.length"
                                    @click="deleteUser()"
                                >
                                    Delete
                                </button>
                                <button
                                    class="ml-1 cursor-pointer rounded-lg bg-red-700 px-5 py-2.5 text-center text-sm font-medium text-white hover:bg-red-800 focus:ring-4 focus:ring-red-300 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-800"
                                    :disabled="formDelete.processing"
                                    @click="deleteUser(true)"
                                >
                                    Delete All
                                </button>
                                <p
                                    v-if="form.hasErrors"
                                    class="mt-2 text-red-500"
                                >
                                    {{ form.errors.file }}
                                </p>
                            </div>

                            <div
                                class="flex-column mt-4 flex flex-wrap items-center justify-between space-y-4 sm:flex-row sm:space-y-0"
                            >
                                <div>
                                    <button
                                        class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-sm font-medium text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-4 focus:ring-gray-100 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:hover:border-gray-600 dark:hover:bg-gray-700 dark:focus:ring-gray-700"
                                        type="button"
                                        @click="toggleDropdown"
                                    >
                                        <span
                                            class="me-3 flex items-center text-sm font-medium text-gray-900 dark:text-white"
                                        >
                                            <span
                                                class="me-1.5 flex h-2.5 w-2.5 shrink-0 rounded-full"
                                                :class="currentStatusStyle.color"
                                            />
                                            {{ currentStatusStyle.text }} ({{
                                                getCount(currentStatusStyle.value)
                                            }})
                                        </span>
                                        <svg
                                            aria-hidden="true"
                                            class="ms-2.5 h-2.5 w-2.5"
                                            fill="none"
                                            viewBox="0 0 10 6"
                                            xmlns="http://www.w3.org/2000/svg"
                                        >
                                            <path
                                                d="m1 1 4 4 4-4"
                                                stroke="currentColor"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                            />
                                        </svg>
                                    </button>
                                    <!-- Dropdown menu -->
                                    <div
                                        v-if="dropdownOpen"
                                        class="absolute z-10 w-48 divide-y divide-gray-100 rounded-lg bg-white shadow-sm dark:divide-gray-600 dark:bg-gray-700"
                                    >
                                        <ul
                                            aria-labelledby="dropdownRadioButton"
                                            class="space-y-1 p-3 text-sm text-gray-700 dark:text-gray-200"
                                        >
                                            <li
                                                v-for="(status, index) in masterStatusFilter"
                                                :key="index"
                                            >
                                                <div
                                                    class="flex items-center rounded-sm p-2 hover:bg-gray-100 dark:hover:bg-gray-600"
                                                >
                                                    <input
                                                        :id="'filter-radio-example-' + index"
                                                        v-model="inputStatusFilter"
                                                        class="h-4 w-4 border-gray-300 bg-gray-100 text-blue-600 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:ring-offset-gray-800 dark:focus:ring-blue-600 dark:focus:ring-offset-gray-800"
                                                        name="filter-radio"
                                                        type="radio"
                                                        :value="status.value"
                                                        @change="selectFilter(status.value)"
                                                    />

                                                    <label
                                                        class="ms-2 w-full rounded-sm text-sm font-medium text-gray-900 dark:text-gray-300"
                                                        :for="'filter-radio-example-' + index"
                                                    >
                                                        <span
                                                            class="me-3 flex items-center text-sm font-medium text-gray-900 dark:text-white"
                                                        >
                                                            <span
                                                                class="me-1.5 flex h-2.5 w-2.5 shrink-0 rounded-full"
                                                                :class="status.color"
                                                            />
                                                            {{ status.text }} ({{
                                                                getCount(status.value)
                                                            }})
                                                        </span>
                                                    </label>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <!-- <label
                                    class="sr-only"
                                    for="table-search"
                                >
                                    Search
                                </label>
                                <div class="relative">
                                    <div
                                        class="rtl:inset-r-0 pointer-events-none absolute inset-y-0 left-0 flex items-center ps-3 rtl:right-0"
                                    >
                                        <svg
                                            aria-hidden="true"
                                            class="h-5 w-5 text-gray-500 dark:text-gray-400"
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                            xmlns="http://www.w3.org/2000/svg"
                                        >
                                            <path
                                                clip-rule="evenodd"
                                                d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                                fill-rule="evenodd"
                                            />
                                        </svg>
                                    </div>
                                    <input
                                        id="table-search"
                                        class="block w-80 rounded-lg border border-gray-300 bg-gray-50 p-2 ps-10 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                                        placeholder="Search for items"
                                        type="text"
                                    />
                                </div> -->
                            </div>
                            <div class="relative mt-3 overflow-x-auto shadow-md sm:rounded-lg">
                                <table
                                    class="w-full text-center text-xs text-gray-500 rtl:text-right dark:text-gray-400"
                                >
                                    <thead
                                        class="bg-gray-50 text-xs uppercase text-gray-700 dark:bg-gray-700 dark:text-gray-400"
                                    >
                                        <tr>
                                            <th
                                                class="p-4"
                                                scope="col"
                                            >
                                                <div class="flex items-center">
                                                    <input
                                                        id="checkbox-all-search"
                                                        :checked="checkedAll"
                                                        class="h-4 w-4 rounded-sm border-gray-300 bg-gray-100 text-blue-600 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:ring-offset-gray-800 dark:focus:ring-blue-600 dark:focus:ring-offset-gray-800"
                                                        type="checkbox"
                                                        @change="toggleAll"
                                                    />
                                                    <label
                                                        class="sr-only"
                                                        for="checkbox-all-search"
                                                    >
                                                        checkbox
                                                    </label>
                                                </div>
                                            </th>
                                            <th
                                                class="px-3 py-3"
                                                scope="col"
                                            >
                                                First name
                                            </th>
                                            <th
                                                class="px-3 py-3"
                                                scope="col"
                                            >
                                                Last name
                                            </th>
                                            <th
                                                class="px-3 py-3"
                                                scope="col"
                                            >
                                                Phone
                                            </th>
                                            <th
                                                class="px-3 py-3"
                                                scope="col"
                                            >
                                                Email
                                            </th>
                                            <th
                                                class="px-3 py-3"
                                                scope="col"
                                            >
                                                Password
                                            </th>
                                            <th
                                                class="px-3 py-3"
                                                scope="col"
                                            >
                                                UID
                                            </th>
                                            <th
                                                class="px-3 py-3"
                                                scope="col"
                                            >
                                                2FA
                                            </th>
                                            <th
                                                class="px-3 py-3"
                                                scope="col"
                                            >
                                                Status
                                            </th>
                                            <th
                                                class="px-3 py-3"
                                                scope="col"
                                            >
                                                Updated At
                                            </th>
                                            <th
                                                class="px-3 py-3"
                                                scope="col"
                                            >
                                                Action
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="data in facebook_data.data"
                                            :key="data.id"
                                            class="border-b border-gray-200 bg-white hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-600"
                                        >
                                            <td class="w-4 p-4">
                                                <div class="flex items-center">
                                                    <input
                                                        id="checkbox-table-search-1"
                                                        v-model="checkedIds"
                                                        class="h-4 w-4 rounded-sm border-gray-300 bg-gray-100 text-blue-600 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:ring-offset-gray-800 dark:focus:ring-blue-600 dark:focus:ring-offset-gray-800"
                                                        type="checkbox"
                                                        :value="data.id"
                                                        @change="toggle"
                                                    />
                                                    <label
                                                        class="sr-only"
                                                        for="checkbox-table-search-1"
                                                    >
                                                        checkbox
                                                    </label>
                                                </div>
                                            </td>
                                            <th
                                                class="px-3 py-4"
                                                scope="row"
                                            >
                                                {{ data.first_name ?? '-' }}
                                            </th>
                                            <td class="px-3 py-4">{{ data.last_name ?? '-' }}</td>
                                            <td class="px-3 py-4">{{ data.phone ?? '-' }}</td>
                                            <td class="px-3 py-4">{{ data.email ?? '-' }}</td>
                                            <td class="px-3 py-4">{{ data.password ?? '-' }}</td>
                                            <td class="px-3 py-4">
                                                {{ data.facebook_uid ?? '-' }}
                                            </td>
                                            <td class="px-3 py-4">
                                                {{ data.two_fa_secret ?? '-' }}
                                            </td>
                                            <td class="px-3 py-4">
                                                <FacebookUserStatus :status="data.status" />
                                            </td>
                                            <td class="px-3 py-4">{{ data.updated_at ?? '-' }}</td>

                                            <td class="px-3 py-4">
                                                <a
                                                    class="font-medium text-blue-600 hover:underline dark:text-blue-500"
                                                    href="#"
                                                >
                                                    Edit
                                                </a>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <nav
                                    v-if="facebook_data.data.length"
                                    aria-label="Table navigation"
                                    class="flex-column flex flex-wrap items-center justify-between pt-4 md:flex-row"
                                >
                                    <span
                                        class="mb-4 block w-full text-sm font-normal text-gray-500 md:mb-0 md:inline md:w-auto dark:text-gray-400"
                                    >
                                        Showing
                                        <span class="font-semibold text-gray-900 dark:text-white">
                                            {{
                                                (facebook_data.current_page - 1) *
                                                    facebook_data.per_page +
                                                1
                                            }}
                                            -
                                            <template
                                                v-if="
                                                    facebook_data.current_page ==
                                                    facebook_data.links.length - 2
                                                "
                                            >
                                                {{ facebook_data.total }}
                                            </template>
                                            <template v-else>
                                                {{
                                                    facebook_data.current_page *
                                                    facebook_data.per_page
                                                }}
                                            </template>
                                        </span>
                                        of
                                        <span class="font-semibold text-gray-900 dark:text-white">
                                            {{ facebook_data.total }}
                                        </span>
                                    </span>
                                    <ul
                                        class="inline-flex h-8 -space-x-px text-sm rtl:space-x-reverse"
                                    >
                                        <li
                                            v-for="(page, index) in facebook_data.links"
                                            :key="index"
                                        >
                                            <Link
                                                v-if="
                                                    index == 0 ||
                                                    index == facebook_data.links.length - 1
                                                "
                                                class="flex h-8 items-center justify-center border border-gray-300 bg-white px-3 leading-tight text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white"
                                                :class="{
                                                    'ms-0 rounded-s-lg': index == 0,
                                                    'rounded-e-lg':
                                                        index == facebook_data.links.length - 1,
                                                }"
                                                :href="page.url ?? ''"
                                                :preserveScroll="true"
                                                v-html="page.label"
                                            />
                                            <Link
                                                v-else
                                                class="flex h-8 items-center justify-center border border-gray-300 px-3 hover:bg-gray-100 hover:text-gray-700 dark:border-gray-700"
                                                :class="{
                                                    'bg-blue-50 text-blue-600 hover:bg-blue-100 hover:text-blue-700 dark:bg-gray-700 dark:text-white':
                                                        page.label == facebook_data.current_page,
                                                    'bg-white leading-tight text-gray-500 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white':
                                                        page.label != facebook_data.current_page,
                                                }"
                                                :href="page.url ?? ''"
                                                :preserveScroll="true"
                                                v-html="page.label"
                                            />
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
