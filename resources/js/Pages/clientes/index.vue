<script>
import Layout from "@/Layouts/main.vue";
import PageHeader from "@/Components/page-header.vue";
import { router } from '@inertiajs/vue3';
import ClienteModal from './modal.vue';

export default {
  components: {
    Layout,
    PageHeader,
    ClienteModal
  },
  props: {
    clientes: Array,
    title: String,
    description: String,
    breadcrumbs: Array
  },
  data() {
    return {
      modalCliente: {
        accion: null, // crear, editar, ver
        idCliente: null
      },
      showModal: false
    }
  },

  methods: {
    router,
    abrirModal(accion, idCliente = null) {
      this.modalCliente.accion = accion;
      this.modalCliente.idCliente = idCliente;

      this.showModal = true;
    }
  }
};
</script>

<template>
  <Layout>
    <PageHeader :title="title" :pageTitle="breadcrumbs[0].name" />
    <ClienteModal v-if="showModal" :idCliente="modalCliente.idCliente" :accion="modalCliente.accion" @cerrarModal="showModal = false" />
    <div class="row">
      <div class="col-lg-12">
        <div class="card" id="tasksList">
          <div class="card-header border-0">
            <div class="d-flex align-items-center">
              <h5 class="card-title mb-0 flex-grow-1">Clientes</h5>
              <div class="flex-shrink-0">
                <div class="d-flex flex-wrap gap-2">
                  <button class="btn btn-danger add-btn" @click="abrirModal('crear', null)"><i
                      class="ri-add-line align-bottom me-1"></i> Nuevo</button>
                  <button class="btn btn-soft-danger" id="remove-actions"><i
                      class="ri-delete-bin-2-line"></i></button>
                </div>
              </div>
            </div>
          </div>
          <div class="card-body border border-dashed border-end-0 border-start-0">
            <form>
              <div class="row g-3">
                <div class="col-xxl-5 col-sm-12">
                  <div class="search-box">
                    <input type="text" class="form-control search bg-light border-light"
                      placeholder="Buscar cliente">
                    <i class="ri-search-line search-icon"></i>
                  </div>
                </div>
                <!--end col-->

                <div class="col-xxl-3 col-sm-4">
                  <input type="text" class="form-control bg-light border-light flatpickr-input" id="demo-datepicker"
                    data-provider="flatpickr" data-date-format="d M, Y" data-range-date="true"
                    placeholder="Seleccionar fecha" readonly="readonly">
                </div>
                <!--end col-->

                <div class="col-xxl-3 col-sm-4">
                  <div class="input-light">
                    <div class="choices" data-type="select-one" tabindex="0" role="listbox" aria-haspopup="true"
                      aria-expanded="false">
                      <div class="choices__inner"><select class="form-control choices__input" data-choices=""
                          data-choices-search-false="" name="choices-single-default" id="idStatus" hidden=""
                          tabindex="-1" data-choice="active">
                          <option value="">Status</option>
                          <option value="all" selected="">All</option>
                          <option value="New">New</option>
                          <option value="Pending">Pending</option>
                          <option value="Inprogress">Inprogress</option>
                          <option value="Completed">Completed</option>
                        </select>
                        <div class="choices__list choices__list--single">
                          <div class="choices__item choices__item--selectable" data-item="" data-id="2" data-value="all"
                            aria-selected="true" role="option">All</div>
                        </div>
                      </div>
                      <div class="choices__list choices__list--dropdown" aria-expanded="false">
                        <div class="choices__list" role="listbox">
                          <div id="choices--idStatus-item-choice-1"
                            class="choices__item choices__item--choice choices__placeholder choices__item--selectable is-highlighted"
                            role="option" data-choice="" data-id="1" data-value="" data-select-text="Press to select"
                            data-choice-selectable="" aria-selected="true">Status</div>
                          <div id="choices--idStatus-item-choice-2"
                            class="choices__item choices__item--choice is-selected choices__item--selectable"
                            role="option" data-choice="" data-id="2" data-value="all" data-select-text="Press to select"
                            data-choice-selectable="">All</div>
                          <div id="choices--idStatus-item-choice-6"
                            class="choices__item choices__item--choice choices__item--selectable" role="option"
                            data-choice="" data-id="6" data-value="Completed" data-select-text="Press to select"
                            data-choice-selectable="">Completed</div>
                          <div id="choices--idStatus-item-choice-5"
                            class="choices__item choices__item--choice choices__item--selectable" role="option"
                            data-choice="" data-id="5" data-value="Inprogress" data-select-text="Press to select"
                            data-choice-selectable="">Inprogress</div>
                          <div id="choices--idStatus-item-choice-3"
                            class="choices__item choices__item--choice choices__item--selectable" role="option"
                            data-choice="" data-id="3" data-value="New" data-select-text="Press to select"
                            data-choice-selectable="">New</div>
                          <div id="choices--idStatus-item-choice-4"
                            class="choices__item choices__item--choice choices__item--selectable" role="option"
                            data-choice="" data-id="4" data-value="Pending" data-select-text="Press to select"
                            data-choice-selectable="">Pending</div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <!--end col-->
                <div class="col-xxl-1 col-sm-4">
                  <button type="button" class="btn btn-primary w-100" onclick="SearchData();"> <i
                      class="ri-equalizer-fill me-1 align-bottom"></i>
                    Filters
                  </button>
                </div>
                <!--end col-->
              </div>
              <!--end row-->
            </form>
          </div>
          <!--end card-body-->
          <div class="card-body">
            <div class="table-responsive table-card mb-4">
              <table class="table align-middle table-nowrap mb-0" id="tasksTable" v-if="clientes.length > 0">
                <thead class="table-light text-muted">
                  <tr>
                    <th class="sort desc" data-sort="id">Identificación</th>
                    <th class="sort" data-sort="project_name">Nombre cliente</th>
                    <th class="sort" data-sort="tasks_name">Correo</th>
                    <th class="sort" data-sort="client_name">Teléfono</th>
                    <th class="sort" data-sort="assignedto">Estado</th>
                    <th class="sort" data-sort="action">Acciones</th>
                  </tr>
                </thead>
                <tbody class="list form-check-all">
                  <tr v-for="cliente in clientes" :key="cliente.id">
                    <td class="id"><a href="apps-tasks-details.html" class="fw-medium link-primary">{{ cliente.identificacion }}</a></td>
                    <td>{{ cliente.nombres }}</td>
                    <td>{{ cliente.email }}</td>
                    <td>{{ cliente.telefono }}</td>
                    <td>
                      <span class="badge bg-success">Activo</span>
                    </td>
                    <td>
                      <span>
                        <a href="javascript:void(0);" class="text-warning" @click="abrirModal('editar', cliente.id)">
                          <i class="ri-pencil-line"></i>
                        </a>
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
              <!--end table-->
              <div class="noresult" v-if="clientes.length === 0">
                <div class="text-center">
                  <lord-icon src="https://cdn.lordicon.com/msoeawqm.json" trigger="loop"
                    colors="primary:#121331,secondary:#08a88a" style="width:75px;height:75px"></lord-icon>
                  <h5 class="mt-2">No se encontraron resultados</h5>
                  <p class="text-muted mb-0">No se encontraron clientes para la búsqueda.</p>
                </div>
              </div>
            </div>
          </div>
          <!--end card-body-->
        </div>
        <!--end card-->
      </div>
      <!--end col-->
    </div>
  </Layout>
</template>
