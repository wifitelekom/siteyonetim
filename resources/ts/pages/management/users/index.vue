<script setup lang="ts">
import type { PaginationMeta } from '@/types/api'
import { isAbortError, useAbortOnUnmount } from '@/composables/useAbortOnUnmount'
import { $api } from '@/utils/api'
import { getApiErrorMessage } from '@/utils/errorHandler'

const { withAbort } = useAbortOnUnmount()

interface UserItem {
  id: number
  name: string
  email: string | null
  phone: string | null
  tc_kimlik: string | null
  role: string | null
  role_label: string
  roles: string[]
  created_at: string | null
  apartment_count: number
}

interface UsersResponse {
  data: UserItem[]
  meta: PaginationMeta
}

interface UsersMetaResponse {
  data: {
    roles: Array<{ value: 'admin' | 'owner' | 'tenant' | 'vendor'; label: string }>
    apartments: Array<{ id: number; label: string }>
    can_manage_roles: boolean
  }
}

const { user: authUser } = useAuthSession()

const loading = ref(false)
const loadingMeta = ref(false)
const deletingId = ref<number | null>(null)
const errorMessage = ref('')

const users = ref<UserItem[]>([])
const roleOptions = ref<Array<{ value: 'admin' | 'owner' | 'tenant' | 'vendor'; label: string }>>([])
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 20,
  total: 0,
})

const filters = ref({
  role: null as string | null,
  search: '',
  archived: false,
})

const fetchMeta = async () => {
  loadingMeta.value = true

  try {
    const response = await $api<UsersMetaResponse>('/users/meta')
    roleOptions.value = response.data.roles
  }
  catch (error) {
    errorMessage.value = getApiErrorMessage(error, 'Filtre verileri alınamadı.')
  }
  finally {
    loadingMeta.value = false
  }
}

const fetchUsers = async (page = 1) => {
  loading.value = true
  errorMessage.value = ''

  try {
    const response = await $api<UsersResponse>('/users', {
      query: {
        page,
        role: filters.value.role || undefined,
        search: filters.value.search || undefined,
        archived: filters.value.archived ? '1' : '0',
      },
    })

    users.value = response.data
    pagination.value = response.meta
  }
  catch (error) {
    errorMessage.value = getApiErrorMessage(error, 'Kullanıcı listesi alınamadı.')
  }
  finally {
    loading.value = false
  }
}

const applyFilters = async () => {
  await fetchUsers(1)
}

const resetFilters = async () => {
  filters.value = {
    role: null,
    search: '',
    archived: false,
  }

  await fetchUsers(1)
}

const deleteUser = async (row: UserItem) => {
  deletingId.value = row.id
  errorMessage.value = ''

  try {
    await $api(`/users/${row.id}`, { method: 'DELETE' })
    await fetchUsers(pagination.value.current_page)
  }
  catch (error) {
    errorMessage.value = getApiErrorMessage(error, 'Kullanıcı silinemedi.')
  }
  finally {
    deletingId.value = null
  }
}

onMounted(async () => {
  await Promise.allSettled([fetchMeta(), fetchUsers(1)])
})
</script>

<template>
  <VRow>
    <VCol cols="12">
      <div class="d-flex align-center justify-space-between mb-2">
        <div>
          <h4 class="text-h4 mb-1">
            Kullanıcılar
          </h4>
          <p class="text-medium-emphasis mb-0">
            Site kullanıcılarini yönetin
          </p>
        </div>

        <VBtn
          color="primary"
          prepend-icon="ri-user-add-line"
          to="/management/users/create"
        >
          {{ $t('pages.users.newTitle') }}
        </VBtn>
      </div>
    </VCol>

    <VCol cols="12">
      <VCard :loading="loadingMeta">
        <VCardText>
          <VRow>
            <VCol
              cols="12"
              md="3"
            >
              <VSelect
                v-model="filters.role"
                :items="roleOptions"
                item-title="label"
                item-value="value"
                label="Rol"
                clearable
              />
            </VCol>

            <VCol
              cols="12"
              md="6"
            >
              <VTextField
                v-model="filters.search"
                :label="$t('common.search')"
                placeholder="Ad, e-posta, telefon, TC"
              />
            </VCol>

            <VCol
              cols="12"
              md="3"
              class="d-flex align-center"
            >
              <VCheckbox
                v-model="filters.archived"
                label="Arsivlenmisleri goster"
                hide-details
              />
            </VCol>

            <VCol cols="12">
              <div class="d-flex gap-3 justify-end">
                <VBtn
                  variant="outlined"
                  @click="resetFilters"
                >
                  {{ $t('common.clear') }}
                </VBtn>
                <VBtn
                  color="primary"
                  @click="applyFilters"
                >
                  {{ $t('common.filter') }}
                </VBtn>
              </div>
            </VCol>
          </VRow>
        </VCardText>
      </VCard>
    </VCol>

    <VCol
      v-if="errorMessage"
      cols="12"
    >
      <VAlert
        type="error"
        variant="tonal"
      >
        {{ errorMessage }}
      </VAlert>
    </VCol>

    <VCol cols="12">
      <VCard :loading="loading">
        <VTable density="comfortable">
          <thead>
            <tr>
              <th>Ad Soyad</th>
              <th>E-posta</th>
              <th>{{ $t('common.contact') }}</th>
              <th>Rol</th>
              <th>{{ $t('common.apartment') }}</th>
              <th class="text-right">
                İşlemler
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="row in users"
              :key="row.id"
            >
              <td>
                <RouterLink
                  :to="`/management/users/${row.id}`"
                  class="font-weight-medium text-primary text-decoration-underline d-inline-block"
                >
                  {{ row.name }}
                </RouterLink>
              </td>
              <td>
                {{ row.email || '-' }}
              </td>
              <td>
                <div>{{ row.phone || '-' }}</div>
                <div class="text-caption text-medium-emphasis">
                  {{ row.tc_kimlik || '-' }}
                </div>
              </td>
              <td>
                <VChip
                  size="small"
                  color="primary"
                  variant="tonal"
                >
                  {{ row.role_label }}
                </VChip>
              </td>
              <td>{{ row.apartment_count }}</td>
              <td class="text-right">
                <VBtn
                  icon
                  size="small"
                  variant="text"
                  :to="`/management/users/${row.id}`"
                >
                  <VIcon icon="ri-eye-line" />
                </VBtn>
                <VBtn
                  icon
                  size="small"
                  variant="text"
                  :to="`/management/users/${row.id}/edit`"
                >
                  <VIcon icon="ri-edit-line" />
                </VBtn>
                <VBtn
                  icon
                  size="small"
                  variant="text"
                  color="error"
                  :disabled="authUser?.id === row.id || deletingId === row.id"
                  :loading="deletingId === row.id"
                  @click="deleteUser(row)"
                >
                  <VIcon icon="ri-delete-bin-line" />
                </VBtn>
              </td>
            </tr>
            <tr v-if="users.length === 0">
              <td
                colspan="6"
                class="text-center text-medium-emphasis py-6"
              >
                {{ $t('common.noRecords') }}
              </td>
            </tr>
          </tbody>
        </VTable>

        <VCardText class="d-flex justify-space-between align-center flex-wrap gap-3">
          <span class="text-sm text-medium-emphasis">{{ $t('common.totalRecords', { count: pagination.total }) }}</span>

          <VPagination
            :model-value="pagination.current_page"
            :length="pagination.last_page"
            :total-visible="7"
            @update:model-value="fetchUsers"
          />
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>
