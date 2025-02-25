import axios from 'axios'
import { ref } from 'vue'

export function useAxios(baseURL = undefined) {
    const requestLoading = ref(false)

    const axiosIns = axios.create({ baseURL })

    axiosIns.interceptors.request.use(
        (config) => {
            requestLoading.value = true
            return config
        },
        (error) => {
            requestLoading.value = false
            return Promise.reject(error)
        },
    )

    axiosIns.interceptors.response.use(
        (response) => {
            requestLoading.value = false
            return response
        },
        (error) => {
            requestLoading.value = false
            return Promise.reject(error)
        },
    )

    return { axiosIns, requestLoading }
}
