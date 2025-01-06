<script setup>
import { ref, computed, onMounted } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Button from "primevue/button";
import Select from 'primevue/select'; // Importa el Select
import Toast from 'primevue/toast';
import InputLabel from "@/Components/InputLabel.vue";
import FormSection from '@/Components/FormSection.vue';
import { useToast } from 'primevue/usetoast';
import DataTable from 'primevue/datatable';
import Dialog from 'primevue/dialog';
import Column from 'primevue/column';
import { useI18n } from 'vue-i18n';
import { InputText } from 'primevue';

function toggleDarkMode() { 
    document.documentElement.classList.toggle('my-app-dark');
    isMoon.value = !isMoon.value;
 } 

const isMoon = ref(true);

const { t, locale } = useI18n();

// Estado para el idioma seleccionado
const selectedLanguage = ref(locale.value);

// Cambiar el idioma
const changeLanguage = (language) => {
    locale.value = language; // Cambia el idioma
    selectedLanguage.value = language; // Actualiza el estado del idioma seleccionado
};

const toast = useToast();

const titulosTipos = ref([]);

const loading = ref(false);

const editMode = ref(false);

const selectedTitulo = ref(null);

const tituloForm = useForm({
    nombre: '', // Aquí se almacena el valor seleccionado en el Select
});

// Mantener una copia de los datos actuales
const currentData = ref(null);

// Datatable
const expandedRows = ref({});

const expandAll = () => {
    expandedRows.value = titulosTipos.value.reduce((acc, p) => (acc[p.id] = true) && acc, {});
};

const collapseAll = () => {
    expandedRows.value = null;
};

// Modal
const visible = ref(false);

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
    tituloForm.nombre = titulo.nombre; // Aquí se asigna el valor que ya está en la base de datos
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
    if (confirm('¿Estás seguro de eliminar este título?')) {
        axios.delete(route('titulos-tipos.destroy', id))
            .then(() => {
                titulosTipos.value = titulosTipos.value.filter(titulo => titulo.id !== id);
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
            });
    }
};
</script>

<template>
    
    <Toast position="top-center" />
        <div class="flex justify-end gap-2">
            <Button  :icon="isMoon ? 'pi pi-moon' : 'pi pi-sun'"
            @click="toggleDarkMode" 
            class="w32 sm:w-auto"
            ref="darkModeButton"/>
        </div>

    <div class="language-selector">
            <label>{{ t('select_language') }}:</label>
            <select v-model="selectedLanguage" @change="changeLanguage(selectedLanguage)">
                <option value="es">{{ t('spanish') }}</option>
                <option value="en">{{ t('english') }}</option>
            </select>
        </div>

    <div v-if="loading" class="flex justify-center items-center p-4">
        <i class="pi pi-spin pi-spinner text-2xl"></i>
        <span class="ml-2">{{ t('loading_data') }} </span>
    </div>

    <div v-else>
        <FormSection @submitted="submitForm">
            <template #title>
                {{ t('academic_info') }}
            </template>

            <template #description>
                {{ t('academic_info_description') }}
            </template>

            <template #form>
                <div class="col-span-12">
                    <DataTable v-model:expandedRows="expandedRows" 
                                :value="titulosTipos" 
                                dataKey="id" 
                                class="text-sm"
                                tableStyle="min-width: 40rem; width: 100%; padding: 0;">
                        <template #header>
                            <div class="flex justify-between items-center mb-6">
                                <h3 class="text-lg font-medium">{{ t('registered_academic_titles') }}</h3>
                                <Button :label="t('add_academic_title')" icon="pi pi-plus" @click="showForm"
                                    class="p-button-sm p-button-primary"/>
                            </div>
                        </template>

                        <template #empty>
                            <div class="text-center p-4">
                                <p class="mb-2">{{ t('no_academic_records') }}</p>
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
           
            :placeholder="t('select_type')" 
             
        />
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
                            class="p-button-text" 
                            />
                            <Button :label="t('save')" 
                            icon="pi pi-check" 
                            @click="submitForm"
                            :loading="tituloForm.processing" 
                            />
                        </div>
                    </template>
                </Dialog>
            </template>
        </FormSection>
    </div>
</template> 