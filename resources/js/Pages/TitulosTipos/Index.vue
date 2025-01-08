<script setup>
import { ref, computed, onMounted } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Button from "primevue/button";
import Toast from 'primevue/toast';
import InputLabel from "@/Components/InputLabel.vue";
import FormSection from '@/Components/FormSection.vue';
import { useToast } from 'primevue/usetoast';
import DataTable from 'primevue/datatable';
import Dialog from 'primevue/dialog';
import Column from 'primevue/column';
import { useI18n } from 'vue-i18n';
import { InputText } from 'primevue';

const { t, locale } = useI18n();

const selectedLanguage = ref(locale.value);

const toggleLanguage = () => {
  if (selectedLanguage.value === 'es') {
    selectedLanguage.value = 'en';
    locale.value = 'en';
  } else {
    selectedLanguage.value = 'es';
    locale.value = 'es';
  }
};

const toast = useToast();

const titulosTipos = ref([]);
const loading = ref(false);
const editMode = ref(false);
const selectedTitulo = ref(null);

const tituloForm = useForm({
  nombre: '',
});

const currentData = ref(null);
const expandedRows = ref({});
const visible = ref(false);

const deleteDialogVisible = ref(false);
const tituloToDelete = ref(null);

const expandAll = () => {
  expandedRows.value = titulosTipos.value.reduce((acc, p) => (acc[p.id] = true) && acc, {});
};

const collapseAll = () => {
  expandedRows.value = null;
};

const showForm = () => {
  visible.value = true;
  tituloForm.reset();
};

const hideDialog = () => {
  visible.value = false;
  editMode.value = false;
  selectedTitulo.value = null;
  tituloForm.reset();
};

onMounted(async () => {
  loading.value = true;
  try {
    const response = await axios.get(route('titulos-tipos.index'));
    titulosTipos.value = response.data;
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: t('error'),
      detail: t('error_loading_data'),
      life: 5000
    });
  } finally {
    loading.value = false;
  }
});

const validateForm = () => {
  const errors = [];
  if (!tituloForm.nombre || tituloForm.nombre.trim() === '') {
    errors.push(t('field_required', { field: t('title') }));
  }
  return errors;
};

const showEditForm = (titulo) => {
  editMode.value = true;
  selectedTitulo.value = titulo.id;
  tituloForm.nombre = titulo.nombre;
  visible.value = true;
};

const submitForm = () => {
  const errors = validateForm();
  if (errors.length > 0) {
    errors.forEach(error => {
      toast.add({
        severity: 'error',
        summary: t('validation_error'),
        detail: error,
        life: 3000
      });
    });
    return;
  }

  if (editMode.value) {
    tituloForm.put(route('titulos-tipos.update', selectedTitulo.value), {
      preserveScroll: true,
      onSuccess: async () => {
        try {
          const response = await axios.get(route('titulos-tipos.index'));
          titulosTipos.value = response.data;
          hideDialog();
          toast.add({
            severity: 'success',
            summary: t('updated'),
            detail: t('update_success'),
            life: 3000
          });
        } catch (error) {
          toast.add({
            severity: 'error',
            summary: t('error'),
            detail: t('update_error'),
            life: 3000
          });
        }
      },
      onError: (errors) => {
        Object.values(errors).forEach(error => {
          toast.add({
            severity: 'error',
            summary: t('error'),
            detail: error,
            life: 3000
          });
        });
      }
    });
  } else {
    tituloForm.post(route('titulos-tipos.store'), {
      preserveScroll: true,
      onSuccess: async () => {
        try {
          const response = await axios.get(route('titulos-tipos.index'));
          titulosTipos.value = response.data;
          hideDialog();
          toast.add({
            severity: 'success',
            summary: t('created'),
            detail: t('create_success'),
            life: 3000
          });
          tituloForm.reset();
        } catch (error) {
          console.error('Error:', error);
        }
      },
      onError: (errors) => {
        Object.values(errors).forEach(error => {
          toast.add({
            severity: 'error',
            summary: t('error'),
            detail: error,
            life: 3000
          });
        });
      }
    });
  }
};

const deleteTitulo = (id) => {
  tituloToDelete.value = id;
  deleteDialogVisible.value = true;
};

const confirmDelete = () => {
  axios.delete(route('titulos-tipos.destroy', tituloToDelete.value))
    .then(() => {
      titulosTipos.value = titulosTipos.value.filter(titulo => titulo.id !== tituloToDelete.value);
      toast.add({
        severity: 'success',
        summary: t('deleted'),
        detail: t('delete_success'),
        life: 3000
      });
    })
    .catch(() => {
      toast.add({
        severity: 'error',
        summary: t('error'),
        detail: t('delete_error'),
        life: 3000
      });
    })
    .finally(() => {
      deleteDialogVisible.value = false;
      tituloToDelete.value = null;
    });
};
</script>

<template>
  <header class="flex justify-end items-center p-4 w-full pr-16">
    <img
      :src="selectedLanguage === 'es' ? '/images/chile-flag.png' : '/images/us-flag.png'"
      alt="Idioma"
      class="w-8 h-6 cursor-pointer"
      @click="toggleLanguage"
    />
  </header>

  <Toast position="top-center" />

  <div v-if="loading" class="flex justify-center items-center p-4">
    <i class="pi pi-spin pi-spinner text-2xl"></i>
    <span class="ml-2">{{ t('loading_data') }} </span>
  </div>

  <div v-else>
    <div class="p-4 rounded-md">
      <FormSection @submitted="submitForm" class="mr-10">
        <template #title>
          <div class="flex justify-start items-center px-6">
            {{ t('academic_info') }}
          </div>
        </template>

        <template #description>
          <div class="flex justify-start items-center px-6 text-sm">
            {{ t('academic_info_description') }}
          </div>
        </template>

        <template #form>
          <div class="col-span-12">
            <DataTable v-model:expandedRows="expandedRows" 
                      :value="titulosTipos" 
                      dataKey="id" 
                      class="text-sm"
                      tableStyle="min-width: 40rem; width: 100%; padding: 0; ">
              <template #header>
                <div class="flex justify-between items-center mb-6">
                  <h3 class="text-lg font-medium">{{ t('types_of_registered_titles') }}</h3>
                  <Button 
                    :label="t('add_title_type')" 
                    icon="pi pi-plus" 
                    @click="showForm"
                    class="p-button-sm p-button-primary" />
                </div>
              </template>

              <template #empty>
                <div class="text-center p-4">
                  <p class="mb-2">{{ t('No_title_types_have_been_recorded') }}</p>
                </div>
              </template>

              <Column expander style="width: 5rem" />
              <Column field="nombre" :header="t('degree_type')" />
              <Column :header="t('actions')" style="width: 8rem">
                <template #body="slotProps">
                  <div class="flex gap-2">
                    <Button icon="pi pi-pencil" class="p-button-warning p-button-text"
                      @click="showEditForm(slotProps.data)" />
                    <Button icon="pi pi-trash" class="p-button-danger p-button-text"
                      @click="deleteTitulo(slotProps.data.id)" />
                  </div>
                </template>
              </Column>
            </DataTable>
          </div>

          <Dialog 
            v-model:visible="visible" 
            :modal="true" 
            :header="t('academic_record')" 
            :style="{ width: '50rem' }"
            :draggable="false" 
            class="p-fluid">
            <div class="space-y-4">
              <div class="field">
                <InputLabel for="nombre" :value="t('degree_type')" />
                <InputText
                  id="nombre" 
                  v-model="tituloForm.nombre" 
                  class="w-full mt-1 block"
                  :class="{'p-invalid': tituloForm.errors.nombre}"
                  :placeholder="t('select_type')" />
                <small class="p-error block" v-if="tituloForm.errors.nombre">
                  {{ tituloForm.errors.nombre }}
                </small>
              </div>
            </div>
            <template #footer>
              <div class="flex justify-end gap-2">
                <Button :label="t('cancel')" 
                  icon="pi pi-times" 
                  @click="hideDialog" 
                  class="p-button-text" />
                <Button :label="t('save')" 
                  icon="pi pi-check" 
                  @click="submitForm"
                  :loading="tituloForm.processing" />
              </div>
            </template>
          </Dialog>

          <Dialog 
            v-model:visible="deleteDialogVisible" 
            :header="t('confirm_delete')" 
            :modal="true" 
            :style="{ width: '30rem' }"
            :draggable="false">
            <div class="flex flex-column align-items-center justify-content-center">
              <p>{{ t('delete_confirmation_message') }}</p>
            </div>
            <template #footer>
              <div class="flex justify-end gap-2">
                <Button :label="t('cancel')" 
                  icon="pi pi-times" 
                  class="p-button-text" 
                  @click="deleteDialogVisible = false" />
                <Button :label="t('confirm')" 
                  icon="pi pi-check" 
                  class="p-button-danger" 
                  @click="confirmDelete" />
              </div>
            </template>
          </Dialog>
        </template>
      </FormSection>
    </div>
  </div>
</template>
