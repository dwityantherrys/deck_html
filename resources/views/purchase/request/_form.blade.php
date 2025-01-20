<?php $baseBeApiUrl = url('/api/backend'); ?>

<?php $prTypeId = !empty(old('request_type')) ? old('request_type') : (!is_null($model->request_type) ? $model->request_type : $model::TYPE_PURCHASE); ?>
<?php $destinationTypeId = !empty(old('destination')) ? old('destination') : (!is_null($model->destination) ? $model->destination : $model::HEAD); ?>
<?php $taxType = !empty(old('tax_type')) ? old('tax_type') : ($model->tax_type ? $model->tax_type : 0); ?>
<?php $salesId = !empty(old('sales_id')) ? old('sales_id') : $model->sales_id; ?>
<?php $patNumber = !empty(old('pat_number')) ? old('pat_number') : $model->pat_number; ?>
<?php $requestBy = !empty(old('request_by')) ? old('request_by') : $model->request_by; ?>
<?php $vendorId = !empty(old('vendor_id')) ? old('vendor_id') : $model->vendor_id; ?>
<?php $branchId = !empty(old('branch_id')) ? old('branch_id') : $model->branch_id; ?>
<?php $purchaseDetails = !empty(old('purchase_details')) ? old('purchase_details') : $model->purchase_details ?>
<?php $itemsName = !empty(old('item_name')) ? old('item_name') : $model->item_name; ?>
<?php $patNumber = !empty(old('remark')) ? old('remark') : $model->remark; ?>

<div class="row">

    <div class="col-md-6">
        <div class="form-group @if($errors->has('request_date')) has-error @endif">
            <label>Request Date</label>

            <div class="input-group date" id="datepicker">
                <div class="input-group-addon">
                    <i class="fa fa-calendar"></i>
                </div>
                <input type="text" class="form-control pull-right" name="request_date"
                    value="{{ empty($model->request_date) ? date('m/d/Y') : $model->request_date_formated }}">
            </div>
            @if($errors->has('request_date'))
            <span class="help-block">{{ $errors->first('request_date') }}</span>
            @endif
        </div>

        <div class="form-group @if($errors->has('pat_number')) has-error @endif">
            <label for="">Pat Number</label>
            <input type="text" class="form-control" name="pat_number" placeholder="No PAT"
                value="{{ !empty(old('pat_number')) ? old('pat_number') : $model->pat_number }}">
            @if($errors->has('pat_number'))
            <span class="help-block">{{ $errors->first('pat_number') }}</span>
            @endif
        </div>

        <div class="form-group @if($errors->has('request_type')) has-error @endif">
            <label>Request Type</label>
            <select class="form-control" name="request_type" style="width: 100%;" tabindex="-1">
                <option value="">Pilih Request Type</option>
                @foreach($prTypes as $typeId => $type)
                <option value="{{ $typeId }}" @if($prTypeId==$typeId) selected @endif>{{ $type['label'] }}</option>
                @endforeach
            </select>
            @if($errors->has('request_type'))
            <span class="help-block">{{ $errors->first('request_type') }}</span>
            @endif
        </div>

        <div class="type-sales form-group @if($errors->has('sales_id')) has-error @endif">
            <label>Sales Quotation Number</label>
            <select class="has-ajax-form form-control" name="sales_id" style="width: 100%;" tabindex="-1"
                data-load="{{ $baseBeApiUrl . '/sales/order' }}">
            </select>
            @if($errors->has('sales_id'))
            <span class="help-block">{{ $errors->first('sales_id') }}</span>
            @endif
        </div>

        <div class="form-group @if($errors->has('request_number')) has-error @endif">
            <label for="">Request Number</label>
            <input type="text" class="form-control" name="request_number" placeholder="request number"
                value="{{ !empty(old('request_number')) ? old('request_number') : $model->request_number }}" readonly>
            @if($errors->has('request_number'))
            <span class="help-block">{{ $errors->first('request_number') }}</span>
            @endif
        </div>

        <div class="form-group @if($errors->has('request_by')) has-error @endif">
            <label>PIC</label>
            <select class="form-control" name="request_by" style="width: 100%;" tabindex="-1"> </select>
            @if($errors->has('request_by'))
            <span class="help-block">{{ $errors->first('request_by') }}</span>
            @endif
            <span class="help-block">data pic tidak ada? <a class="text-red" href="{{ url('/master/employee/create') }}"
                    target="_blank">new pic</a></span>
        </div>

    </div>

    <div class="col-md-6">

        <div class="form-group @if($errors->has('vendor_id')) has-error @endif">
            <label>Vendor</label>
            <select class="form-control" name="vendor_id" style="width: 100%;" tabindex="-1"> </select>
            @if($errors->has('vendor_id'))
            <span class="help-block">{{ $errors->first('vendor_id') }}</span>
            @endif
            <span class="help-block">data vendor tidak ada? <a class="text-red"
                    href="{{ url('/master/customer/create') }}" target="_blank">new vendor</a></span>
        </div>

        <div class="form-group @if($errors->has('branch_id')) has-error @endif">
            <label>Cabang</label>
            <select class="form-control" name="branch_id" style="width: 100%;" tabindex="-1"> </select>
            @if($errors->has('branch_id'))
            <span class="help-block">{{ $errors->first('branch_id') }}</span>
            @endif
            <span class="help-block">data cabang tidak ada? <a class="text-red"
                    href="{{ url('/master/branch/create') }}" target="_blank">buat cabang</a></span>
        </div>

        <div class="form-group @if($errors->has('destination')) has-error @endif">
            <label>Pengiriman</label>
            <select class="form-control" name="destination" style="width: 100%;" tabindex="-1">
                <option value="">Pilih Pengiriman</option>
                @foreach($desinationTypes as $typeId => $type)
                <option value="{{ $typeId }}" @if($destinationTypeId==$typeId) selected @endif>{{ $type['label'] }}
                </option>
                @endforeach
            </select>
            @if($errors->has('destination'))
            <span class="help-block">{{ $errors->first('destination') }}</span>
            @endif
        </div>

        <div class="form-group @if($errors->has('remark')) has-error @endif">
            <label for="">Catatan</label>
            <input type="text" class="form-control" name="remark" placeholder="remark"
                value="{{ !empty(old('remark')) ? old('remark') : $model->remark }}">
            @if($errors->has('remark'))
            <span class="help-block">{{ $errors->first('remark') }}</span>
            @endif
        </div>



        <div class="form-group @if($errors->has('tax_type')) has-error @endif">
            <label>Pilih Pajak</label>
            <select class="form-control " name="tax_type" id="" style="width: 100%;" tabindex="-1">
                <option value="0" @if($taxType=="0" ) selected @endif>None</option>
                <option value="1" @if($taxType=="1" ) selected @endif>PPn 11%</option>
                <option value="2" @if($taxType=="2" ) selected @endif>PPn 11% Include</option>
            </select>
            @if($errors->has('tax_type'))
            <span class="help-block">{{ $errors->first('tax_type') }}</span>
            @endif
        </div>

    </div>

</div>

<hr>

<div id="vue-dynamic-element">
    <!-- list sales order -->

    <hr class="type-sales">

    <div class="form-group">
        <label>List Item</label>

        <table id="vue-dynamic-element" class="table table-bordered table-hover">
            <thead class="table-header-primary">
                <tr>
                    <th width="50%">Item Material</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Amount</th>
                    <th width="10%"></th>
                </tr>
            </thead>

            <tbody>
                <tr v-for="(element, i) in elements" :key="element . id">
                    <td>
                        <input type="hidden" :name="`purchase_details[${i}][id]`" v-model="element.id">

                        <vue-select2 :url="`{{ $baseBeApiUrl . '/items-sparepart' }}`"
                            :name="`purchase_details[${i}][item_material_id]`" :value="element . item_material_id"
                            v-on:selected="getItemByName(i, $event)" :readonly="true" />
                        <!--<input type="text" class="form-control" :name="`purchase_details[${i}][item_name]`" v-model="element.item_name" placeholder="item Name">-->
                    </td>
                    <td>
                        <input type="text" class="form-control" :name="`purchase_details[${i}][quantity]`"
                            v-model="element.quantity" @change="isNumber(i, 'quantity')" placeholder="quantity">
                    </td>
                    <td>
                        <input type="text" class="form-control" :name="`purchase_details[${i}][estimation_price]`"
                            v-model="element.estimation_price" @change="isNumber(i, 'estimation_price')"
                            placeholder="estimation price">
                    </td>
                    <td>
                        <input type="text" class="form-control" :name="`purchase_details[${i}][amount]`"
                            :value="getAmount(i)" placeholder="amount" readonly>
                    </td>
                    <td>
                        <button type="button" class="btn btn-default text-red" @click="removeElement(i)"><i
                                class="fa fa-minus"></i></button>
                    </td>
                </tr>

                <tr>
                    <th>
                        @if($errors->has('purchase_details.*'))
                        <span class="help-block text-red">* {{ $errors->first('purchase_details.*') }}</span>
                        @endif
                    </th>
                    <th>
                        @{{ getTotalQuantity() | formatRupiah }}
                    </th>
                    <th>
                        <label for="">@{{ getTotalAmount() | formatRupiah }}</label>
                    </th>
                    <th>
                        <label for="">Rp. @{{ getTotalAmount() | formatRupiah }}</label>
                        <input type="hidden" name="total_price" :value="getTotalAmount()">
                    </th>
                    <th>
                        <button type="button" class="btn btn-default text-success" @click="addElement()"><i
                                class="fa fa-plus"></i></button>
                    </th>
                </tr>

                <tr>
                    <th colspan="3"><label>Total Amount (Before Tax)</label></th>
                    <th><label for="">Rp. @{{ getTotalAmount() | formatRupiah }}</label></th>
                </tr>

                <tr>
                    <th colspan="3"><label>Amount Pajak</label></th>
                    <th><label for="">Rp. @{{ getTax() | formatRupiah }}</label></th>
                    <input type="hidden" name="amount_tax" :value="getTax()">
                </tr>

                <tr>
                    <th colspan="3"><label>Grand Total (After Tax)</label></th>
                    <th><label for="">Rp. @{{ getGrandAmount() | formatRupiah }}</label></th>
                    <input type="hidden" name="bill" :value="getGrandAmount()">
                </tr>

            </tbody>
        </table>
    </div>

</div> <!-- end vue wrapper -->

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css') }}">
<!-- Tambahkan Bootstrap CSS jika belum ada -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
<!-- Datepicker CSS -->
<link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">


@endsection

@section('js')
<script src="{{ asset('vendor/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}"></script>
<script src="{{ asset('js/vue.js') }}"></script>
<script>
$(document).ready(function() {
    $('#datepicker').datepicker({
        format: 'mm/dd/yyyy', // format tanggal sesuai kebutuhan
        autoclose: true, // menutup otomatis setelah memilih tanggal
        todayHighlight: true
    });
});
var TYPE_PURCHASE = "{{ $model::TYPE_PURCHASE }}";

var isPrTypeSales = false;
var baseBeApiUrl = "{{ $baseBeApiUrl }}";
var elements = <?php echo json_encode($purchaseDetails); ?>;
var tax_type = "{{ !empty(old('tax_type')) ? old('tax_type') : $model->tax_type }}";

$(document).ready(function() {
    // togglePrType("{{ $prTypeId }}")
    $('.type-sales').hide();
    app.isPrTypeSales = false;

    $(".datepicker").datepicker({
        autoClose: true
    });
    $('[data-toggle="tooltip"]').tooltip();
    $('select[name="request_type"]').select2();
    $('select[name="tax_type"]').change(function() {
        var tax_type = $(this).val();
        app.tax_type = tax_type;
    });

    select2AjaxHandler('select[name="vendor_id"]', `{{ $baseBeApiUrl . '/customer' }}`, '{{ $vendorId }}');
    select2AjaxHandler('select[name="branch_id"]', `{{ $baseBeApiUrl . '/branch' }}`, '{{ $branchId }}');
    select2AjaxHandler('select[name="request_by"]', `{{ $baseBeApiUrl . '/employee' }}`, '{{ $requestBy }}');
    select2AjaxHandler('select[name="sales_id"]', `{{ $baseBeApiUrl . '/sales/order' }}`, '{{ $salesId }}');

    $('select[name="request_type"]').change(function() {
        var type = $(this).val();
        $('.type-sales').hide();
        app.isPrTypeSales = false;
        // togglePrType(type);
    });

    $(".has-ajax-form").change(function() {
        var url = $(this).data('load') + '/' + $(this).val()

        $.ajax({
            type: "GET",
            url: url,
            success: function(response) {
                // set value form
                select2AjaxHandler('select[name="created_by"]',
                    `{{ $baseBeApiUrl . '/employee' }}`, response.created_by);
                app.salesOrders = response.sales_details

                $(`a[href="#collapseListOrders"]`).removeClass('disabled')
            },
            error: function(err) {
                console.log(`failed fetch : ${err}`)
            }
        });
    });
})

// function togglePrType(type) {  
//   if(type == TYPE_SALES) {
//     $('.type-sales').show();
//     app.isPrTypeSales = true;
//   } else if(type == TYPE_PURCHASE) {
// $('.type-sales').hide();
// app.isPrTypeSales = false;
//   }
// }


Vue.filter('formatRupiah', function(value) {
    if (!value) return '';

    return new Intl.NumberFormat('en-US', {
        style: 'decimal', // Menggunakan gaya decimal untuk menghindari simbol mata uang
        minimumFractionDigits: 0 // Jika kamu tidak ingin desimal, atur ini ke 0
    }).format(value);
});



Vue.component('vue-select2', {
    template: `<select class="form-control" :name="name" style="width: 100%"> </select>`,
    props: ['url', 'name', 'value'],
    methods: {
        getDataById(id) {
            var vm = this

            $.ajax({
                type: "GET",
                url: `${this.url}/${id}`,
                success: function(response) {
                    var newOption = new Option(response.name, response.id, true, true);
                    $(vm.$el).append(newOption).trigger('change');
                },
                error: function(err) {
                    console.log(`failed fetch : ${err}`)
                }
            });
        },
    },
    mounted: async function() {
        var vm = this

        await $(this.$el).select2({
                ajax: {
                    url: this.url,
                    data: function(params) {
                        var query = {
                            searchKey: params.term
                        }
                        return query;
                    },
                    processResults: function(data) {
                        return {
                            results: data.results.map((param) => {
                                param.text = param.name
                                return param
                            })
                        }
                    }
                },
            })
            .val(this.value)
            .trigger('change')
            .on('change', function() {
                vm.$emit('selected', this.value)
            })

        if (this.value) await this.getDataById(this.value)
    },
    destroyed: function() {
        $(this.$el).off().select2('destroy')
    },
    watch: {
        value: function(value) {
            // update value
            $(this.$el).val(value).trigger('change')
        },
        options: function(options) {
            // update options
            $(this.$el).empty().select2({
                data: options
            })
        },
    },
});

var app = new Vue({
    el: '#vue-dynamic-element',
    data: {
        salesOrders: [],
        elements: elements,
        tax_type: tax_type,
    },
    mounted: function() {
        // check if vue working
        console.log(`${this.$el.id} mounted`)

        if (!this.elements.length) this.addElement()
    },
    methods: {
        _randomNumber() {
            return Math.floor(Math.random() * 10)
        },
        _getItemBoms(itemMaterialId) {
            return $.ajax({
                type: "GET",
                url: `${baseBeApiUrl}/production/bom/item-material/${itemMaterialId}`,
                success: function(response) {
                    return response
                },
                error: function(err) {
                    console.log(`failed fetch : ${err}`)
                }
            });
        },
        //sales orders method
        applyElement(applyAll = false) {
            var vm = this
            // if((this._anyOrdersChecked() || applyAll) && !this.elements[0].sales_detail_id) this.elements.pop()
            vm.elements = []

            vm.salesOrders.forEach(function(salesOrder, index) {
                /** 
                 * applyAll is true, loop all salesOrders and push into elements
                 * is element exist (has pushed) ?
                 * if not exist push into element
                 * */
                elementExist = vm.elements.find(function(element, elindex) {
                    return element.sales_detail_id == salesOrder.id
                })


                if (!elementExist && (applyAll || salesOrder.is_check)) {

                    vm._getItemBoms(salesOrder.item_material_id).then(function(bom) {
                        // check response is empty
                        if (Object.entries(bom).length === 0 && bom.constructor === Object)
                            return;

                        bom.bom_details.forEach(function(bom, inbom) {
                            var quantityNeed = salesOrder.quantity * bom.quantity;

                            vm.elements.push({
                                id: vm._randomNumber(),
                                sales_detail_id: salesOrder.id,
                                raw_material_id: bom.material_id,
                                quantity: quantityNeed,
                                estimation_price: 0
                            })
                        })
                    });

                }
            })
        },
        addElement() {
            this.elements.push({
                id: this._randomNumber(),
                raw_material_id: '',
                quantity: 0,
                estimation_price: 0,
                tax_type: 0,
            })
        },
        removeElement(index) {
            if (this.elements.length == 1) return

            this.elements.splice(index, 1)
        },
        isObjectExist(obj) {
            if (typeof obj == 'undefined') {
                return false
            }

            if (Object.keys(obj).length === 0) {
                return false
            }

            return true
        },
        isNumber(index, attribut) {
            var cleanComa = this.elements[index][attribut].replace(/,/g, "");
            var castNumber = Number(cleanComa);

            if (isNaN(castNumber)) {
                this.elements[index][attribut] = 0;
                return
            }

            this.elements[index][attribut] = this.$options.filters.formatRupiah(castNumber)
        },
        // calculation method
        recalcQuantityElement(arrayOfElement) {
            var result = 0
            var quantities = arrayOfElement.map((element) => {
                var quantity = isNaN(element.quantity) ? element.quantity.replace(/,/g, "") : element
                    .quantity;
                return Number(quantity)
            })

            result = quantities.reduce((result, quantity) => {
                return result + quantity
            }, 0)

            return result
        },
        getAmount(i, element = 'elements') {
            var result = 0

            var qty = isNaN(this.elements[i].quantity) ? this.elements[i].quantity.replace(/,/g, "") : this
                .elements[i].quantity;
            var estPrice = isNaN(this.elements[i].estimation_price) ? this.elements[i].estimation_price.replace(
                /,/g, "") : this.elements[i].estimation_price;
            result = qty * estPrice;

            return this.$options.filters.formatRupiah(result)

        },

        getTotalQuantity(element = 'elements') {
            return this.recalcQuantityElement(this[element])
        },
        getTotalAmount(element = 'elements') {
            var vm = this

            var amounts = this[element].map((item, i) => {
                var amount = vm.getAmount(i, element).replace(/,/g, "")
                return Number(amount)
            })

            var result = amounts.reduce((result, amount) => {
                return result + amount
            }, 0)

            return result
        },

        getTax() {
            var vm = this
            var totalAmountWithDiscount = vm.getTotalAmount()
            var resultWithTax = totalAmountWithDiscount * (11 / 100)
            var resultWithTaxInclude = totalAmountWithDiscount - ((11 / 100) * totalAmountWithDiscount);
            if (vm.tax_type == 1) {
                return resultWithTax;
            } else if (vm.tax_type == 2) {
                return totalAmountWithDiscount - resultWithTaxInclude;
            } else {
                return 0;
            }

        },

        getGrandAmount() {
            if (this.tax_type == 1) {
                var totalAmountWithDiscount = this.getTotalAmount()
                var resultWithTax = (totalAmountWithDiscount * (11 / 100))
                return (this.getTotalAmount()) + resultWithTax
            } else if (this.tax_type == 2) {
                var totalAmountWithDiscount = this.getTotalAmount()
                var resultWithTaxInclude = totalAmountWithDiscount - ((11 / 100) * totalAmountWithDiscount);
                var ppnnotinclude = (11 / 111) * totalAmountWithDiscount;

                return resultWithTaxInclude
            } else {
                return this.getTotalAmount()
            }
        },

        getItemByName(i, itemId) {
            var vm = this

            $.ajax({
                url: `${baseBeApiUrl}/items-sparepart/${itemId}`,
                type: "GET",
                success: function(response) {
                    vm.elements[i].quantity = response.quantity
                    vm.elements[i].item_name = response.name
                    vm.elements[i].estimation_price = response.price

                },
                error: function(err) {
                    console.log(`[Item data] failed fetch : ${err}`)
                }
            });
        }
    }
})
</script>
@endsection