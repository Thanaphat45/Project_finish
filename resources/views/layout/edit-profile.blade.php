@extends('layouts.app')

@section('title', 'แก้ไขโปรไฟล์')

@push('styles')
    <style>
        .profile-form .form-group { margin-bottom: 14px; }
        .profile-form label { display: block; margin-bottom: 6px; }
        .profile-preview { width: 96px; height: 96px; border-radius: 50%; object-fit: cover; margin-top: 10px; display: block; }
        .profile-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 18px; }
    </style>
@endpush

@section('content')
    <div class="form-narrow">
        <a href="{{ url('profile-user') }}" class="back-link"><span class="material-icons">arrow_back</span>โปรไฟล์ของฉัน</a>
        <div class="page-head"><div><h1>แก้ไขโปรไฟล์</h1></div></div>
        <section class="panel panel-pad profile-form">
        <form action="{{ route('update-profile') }}" method="POST" enctype="multipart/form-data"
            onsubmit="concatenateAddress()">
            @csrf
            <div class="form-group">
                <label for="Name_User">ชื่อ-สกุล</label>
                <input type="text" id="Name_User" name="Name_User" class="form-control" value="{{ $user->Name_User }}" required>
            </div>
            <div class="form-group">
                <label for="Email">อีเมล</label>
                <input type="email" id="Email" name="Email" class="form-control" value="{{ $user->Email }}" required>
            </div>

            <div class="form-group">
                <label for="Province">จังหวัด:</label>
                <select id="Province" name="Province" class="form-control" required>
                    <option value="">เลือกจังหวัด</option>
                </select>
            </div>

            <div class="form-group">
                <label for="District">อำเภอ:</label>
                <select id="District" name="District" class="form-control" required>
                    <option value="">เลือกอำเภอ</option>
                </select>
            </div>

            <div class="form-group">
                <label for="Subdistrict">ตำบล:</label>
                <select id="Subdistrict" name="Subdistrict" class="form-control" required>
                    <option value="">เลือกตำบล</option>
                </select>
            </div>

            <div class="form-group">
                <label for="Address">ที่อยู่ (บ้านเลขที่, ถนน, ซอย):</label>
                <textarea id="Address" name="Address" class="form-control" required>{{ $user->Address }}</textarea>
            </div>

            <div class="form-group">
                <label for="Postal_Code">รหัสไปรษณีย์:</label>
                <input type="text" id="Postal_Code" name="Postal_Code" class="form-control"
                    value="{{ $user->Postal_Code }}" readonly>
            </div>

            <div class="form-group">
                <label for="Phone">เบอร์โทร</label>
                <input type="text" id="Phone" name="Phone" class="form-control" value="{{ $user->Phone }}">
            </div>

            <div class="form-group">
                <label for="Image_User">รูปโปรไฟล์</label>
                <label for="Image_User" class="btn btn-light">เลือกรูปภาพ</label>
                <input type="file" id="Image_User" name="Image_User" class="form-control" accept="image/*"
                    style="display: none;" onchange="previewImage(event)">
                <img id="image-preview" src="{{ $user->image_url }}" alt="Image Preview" class="profile-preview" />
            </div>



            <div class="profile-actions"><a href="{{ url('profile-user') }}" class="btn btn-light">ยกเลิก</a><button type="submit" class="btn btn-primary"><span class="material-icons">save</span>บันทึกโปรไฟล์</button></div>

        </form>
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        let provincesData;

        // Load provinces data from the url path
        $.getJSON("{{ url('API/api_province_with_amphure_tambon.json') }}", function (data) {
            provincesData = data;
            let provinceSelect = $('#Province');

            // Populate province dropdown
            provincesData.forEach(function (province) {
                provinceSelect.append(`<option value="${province.id}">${province.name_th}</option>`);
            });

            // Set initial values based on the existing data
            extractAddressParts(@js($user->Address ?? ''));
        });

        function extractAddressParts(address) {
            const provinceSelect = $('#Province');
            const districtSelect = $('#District');
            const subdistrictSelect = $('#Subdistrict');
            const postalCodeField = $('#Postal_Code');
            const addressField = $('#Address');

            // Split the address string into parts
            const parts = address.match(/จังหวัด(.*?) อำเภอ(.*?) ตำบล(.*?) (.*?) รหัสไปรษณีย์ (\d+)/);

            if (parts && parts.length === 6) {
                const provinceName = parts[1];
                const districtName = parts[2];
                const subdistrictName = parts[3];
                const detailedAddress = parts[4]; // This is the part that includes the house number, street, etc.
                const postalCode = parts[5];

                // Set detailed address (house number, street, etc.)
                addressField.val(detailedAddress.trim());

                // Set province, district, subdistrict dropdowns
                const province = provincesData.find(prov => prov.name_th === provinceName.trim());
                if (province) {
                    provinceSelect.val(province.id).trigger('change');
                    setTimeout(function () {
                        const district = province.amphure.find(dist => dist.name_th === districtName.trim());
                        if (district) {
                            districtSelect.val(district.id).trigger('change');
                            setTimeout(function () {
                                const subdistrict = district.tambon.find(subdist => subdist.name_th === subdistrictName.trim());
                                if (subdistrict) {
                                    subdistrictSelect.val(subdistrict.id);

                                    // Set postal code after subdistrict is set
                                    postalCodeField.val(subdistrict.zip_code); // Set postal code based on selected subdistrict
                                }
                            },); //ตั้งการหน่วงเวลาในการทำงาน
                        }
                    },); // ตั้งการหน่วงเวลาในการทำงาน
                }
            }
        }

        // Handle province selection
        $('#Province').change(function () {
            let provinceId = $(this).val();
            let districtSelect = $('#District');
            let subdistrictSelect = $('#Subdistrict');
            districtSelect.empty().append('<option value="">เลือกอำเภอ</option>');
            subdistrictSelect.empty().append('<option value="">เลือกตำบล</option>');

            let selectedProvince = provincesData.find(prov => prov.id == provinceId);
            if (selectedProvince) {
                selectedProvince.amphure.forEach(function (district) {
                    districtSelect.append(`<option value="${district.id}">${district.name_th}</option>`);
                });
            }
        });

        // Handle district selection
        $('#District').change(function () {
            let districtId = $(this).val();
            let subdistrictSelect = $('#Subdistrict');
            subdistrictSelect.empty().append('<option value="">เลือกตำบล</option>');
            let postalCodeField = $('#Postal_Code'); // Select postal code field
            postalCodeField.val(''); // Clear the postal code

            let selectedProvince = provincesData.find(prov => prov.id == $('#Province').val());
            let selectedDistrict = selectedProvince.amphure.find(dist => dist.id == districtId);
            if (selectedDistrict) {
                selectedDistrict.tambon.forEach(function (subdistrict) {
                    subdistrictSelect.append(`<option value="${subdistrict.id}" data-zipcode="${subdistrict.zip_code}">${subdistrict.name_th}</option>`);
                });
            }
        });

        // Handle subdistrict (Tambon) selection and set postal code
        $('#Subdistrict').change(function () {
            let selectedZipCode = $(this).find(':selected').data('zipcode');

            // Set the postal code automatically after subdistrict is selected
            if (selectedZipCode) {
                $('#Postal_Code').val(selectedZipCode);
            }
        });



        // Function to concatenate address parts before form submission
        function concatenateAddress() {
            let province = $('#Province option:selected').text();
            let district = $('#District option:selected').text();
            let subdistrict = $('#Subdistrict option:selected').text();
            let postalCode = $('#Postal_Code').val();
            let detailedAddress = $('#Address').val();

            let fullAddress = `จังหวัด${province} อำเภอ${district} ตำบล${subdistrict} ${detailedAddress} รหัสไปรษณีย์ ${postalCode}`;
            $('#Address').val(fullAddress);
        }

        function previewImage(event) {
            const imagePreview = document.getElementById('image-preview');
            const file = event.target.files[0];

            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    imagePreview.src = e.target.result;
                    imagePreview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            } else {
                imagePreview.style.display = 'none';
            }
        }

    </script>
@endpush
