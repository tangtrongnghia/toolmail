<script setup>
import { ref } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import FacebookUserStatus from '@/Components/FacebookUserStatus.vue'
import { toast } from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'

const props = defineProps({
    facebook_data: {
        type: Object,
    },
})

const file = ref(null)
const form = useForm({ file: null })
const formDelete = useForm({ ids: null, all: false })
const checkedIds = ref([])
const checkedAll = ref(false)

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

const exportCsv = () => {
    window.open(route('fb_user.export'), '_blank')
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
                                    List FB user
                                </h5>
                            </div>
                            <div class="rounded border border-gray-200 p-4 dark:border-gray-700">
                                <h2 class="mb-3 text-lg font-bold">Import Users CSV</h2>

                                <input
                                    id="file_input"
                                    accept=".csv,.xlsx"
                                    class="rounded-lg border border-gray-300 bg-gray-50 p-1 text-gray-900 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-400 dark:placeholder-gray-400"
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

                            <div class="relative mt-3 overflow-x-auto shadow-md sm:rounded-lg">
                                <table
                                    class="w-full text-left text-sm text-gray-500 rtl:text-right dark:text-gray-400"
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
                                                Email
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
                                                PW
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
                                                Status
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
                                            <td class="px-3 py-4">{{ data.email ?? '-' }}</td>
                                            <td class="px-3 py-4">{{ data.phone ?? '-' }}</td>
                                            <td class="px-3 py-4">{{ data.password ?? '-' }}</td>
                                            <td class="px-3 py-4">
                                                {{ data.facebook_uid ?? '-' }}
                                            </td>
                                            <td class="px-3 py-4">
                                                <FacebookUserStatus :status="data.status" />
                                            </td>
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
