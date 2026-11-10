<!doctype html>
<html lang="en">

    <head>

        <meta charset="utf-8" />
        <title>Checkout | Cyprus Digital Solution</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="Cyprus Digital Solution CRM & ERP" name="description" />
        <meta content="Themesdesign" name="author" />
        <!-- App favicon -->
        <link rel="shortcut icon" href="assets/images/favicon-cyprus.svg">

        <!-- Bootstrap Css -->
        <link href="assets/css/bootstrap.min.css" id="bootstrap-style" rel="stylesheet" type="text/css" />
        <!-- Icons Css -->
        <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
        <!-- App Css-->
        <link href="assets/css/app.min.css" id="app-style" rel="stylesheet" type="text/css" />

    </head>

    
    <body>

    <!-- <body data-layout="horizontal"> -->

        <!-- Begin page -->
        <div id="layout-wrapper">

            
            @include('partials.topbar')
            <!-- ========== Left Sidebar Start ========== -->
            <div class="vertical-menu">

                <!-- LOGO -->
                <div class="navbar-brand-box">
                    <a href="/" class="logo logo-dark">
                        <span class="logo-sm">
                            <img src="assets/images/logo-cyprus-mark.svg" alt="" height="26">
                        </span>
                        <span class="logo-lg">
                            <img src="assets/images/logo-cyprus.svg" alt="" height="28">
                        </span>
                    </a>

                    <a href="/" class="logo logo-light">
                        <span class="logo-lg">
                            <img src="assets/images/logo-cyprus-light.svg" alt="" height="30">
                        </span>
                        <span class="logo-sm">
                            <img src="assets/images/logo-cyprus-mark.svg" alt="" height="26">
                        </span>
                    </a>
                </div>

                <button type="button" class="btn btn-sm px-3 font-size-24 header-item waves-effect vertical-menu-btn">
                    <i class="bx bx-menu align-middle"></i>
                </button>

                <div data-simplebar class="sidebar-menu-scroll">

                    <!--- Sidemenu -->
                    @include('partials.sidebar')
                </div>
            </div>
            <!-- Left Sidebar End -->

            <!-- ============================================================== -->
            <!-- Start right Content here -->
            <!-- ============================================================== -->
            <div class="main-content">
                <div class="page-content">
                    <div class="container-fluid">

                        <div class="row">
                            <div class="col-xl-3 col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <i class="fab fa-cc-visa text-primary h2"></i>
                                        <h3 class="mt-3 font-size-20">**** **** **** 2355</h3>
                                        <div class="d-flex justify-content-between mt-4">
                                            <p class="mb-0">Charles Daugherty</p>
                                            <p class="mb-0">Expiry date: 12/25 </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-3 col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <i class="fab fa-cc-mastercard text-primary h2"></i>
                                        <h3 class="mt-3 font-size-20">**** **** **** 9652</h3>
                                        <div class="d-flex justify-content-between mt-4">
                                            <p class="mb-0">Robert Whitney</p>
                                            <p class="mb-0">Expiry date: 12/27 </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-3 col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <i class="fab fa-cc-discover text-primary h2"></i>
                                        <h3 class="mt-3 font-size-20">**** **** **** 6523</h3>
                                        <div class="d-flex justify-content-between mt-4">
                                            <p class="mb-0">Kevin Johnson</p>
                                            <p class="mb-0">Expiry date: 12/28 </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-3 col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <i class="fab fa-cc-amex text-primary h2"></i>
                                        <h3 class="mt-3 font-size-20">**** **** **** 4586</h3>
                                        <div class="d-flex justify-content-between mt-4">
                                            <p class="mb-0">Donald Leggett</p>
                                            <p class="mb-0">Expiry date: 12/29 </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-xl-8">
                                <div class="card">
                                    <div class="card-body">
                                        <ol class="activity-checkout mb-0 px-4 mt-2">
                                            <li class="checkout-item">
                                                <div class="avatar checkout-icon p-1">
                                                    <div class="avatar-title rounded-circle bg-primary">
                                                        <h5 class="text-white font-size-16 mb-0">01</h5>
                                                    </div>
                                                </div>
                                                <div class="feed-item-list">
                                                    <div>
                                                        <h5 class="font-size-16 mb-1">Billing Info</h5>
                                                        <p class="text-muted text-truncate mb-4">Sed ut perspiciatis unde omnis iste</p>
                                                        <div class="mb-3">
                                                            <form>
                                                                <div>
                                                                    <div class="row">
                                                                        <div class="col-lg-4">
                                                                            <div class="mb-3">
                                                                                <label class="form-label" for="billing-name">Name</label>
                                                                                <input type="text" class="form-control" id="billing-name" placeholder="Enter name">
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-lg-4">
                                                                            <div class="mb-3">
                                                                                <label class="form-label" for="billing-email-address">Email Address</label>
                                                                                <input type="email" class="form-control" id="billing-email-address" placeholder="Enter email">
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-lg-4">
                                                                            <div class="mb-3">
                                                                                <label class="form-label" for="billing-phone">Phone</label>
                                                                                <input type="text" class="form-control" id="billing-phone" placeholder="Enter Phone no.">
                                                                            </div>
                                                                        </div>
                                                                    </div>
            
                                                                    <div class="mb-3">
                                                                        <label class="form-label" for="billing-address">Address</label>
                                                                        <textarea class="form-control" id="billing-address" rows="3" placeholder="Enter full address"></textarea>
                                                                    </div>
            
                                                                    <div class="row">
                                                                        <div class="col-lg-4">
                                                                            <div class="mb-4 mb-lg-0">
                                                                                <label class="form-label">Country</label>
                                                                                <select class="form-control form-select" title="Country">
                                                                                    <option value="0">Select Country</option>
                                                                                    <option value="AF">Afghanistan</option>
                                                                                    <option value="AL">Albania</option>
                                                                                    <option value="DZ">Algeria</option>
                                                                                    <option value="AS">American Samoa</option>
                                                                                    <option value="AD">Andorra</option>
                                                                                    <option value="AO">Angola</option>
                                                                                    <option value="AI">Anguilla</option>
                                                                                    <option value="AQ">Antarctica</option>
                                                                                    <option value="AR">Argentina</option>
                                                                                    <option value="AM">Armenia</option>
                                                                                    <option value="AW">Aruba</option>
                                                                                    <option value="AU">Australia</option>
                                                                                    <option value="AT">Austria</option>
                                                                                    <option value="AZ">Azerbaijan</option>
                                                                                    <option value="BS">Bahamas</option>
                                                                                    <option value="BH">Bahrain</option>
                                                                                    <option value="BD">Bangladesh</option>
                                                                                    <option value="BB">Barbados</option>
                                                                                    <option value="BY">Belarus</option>
                                                                                    <option value="BE">Belgium</option>
                                                                                    <option value="BZ">Belize</option>
                                                                                    <option value="BJ">Benin</option>
                                                                                    <option value="BM">Bermuda</option>
                                                                                    <option value="BT">Bhutan</option>
                                                                                    <option value="BO">Bolivia</option>
                                                                                    <option value="BW">Botswana</option>
                                                                                    <option value="BV">Bouvet Island</option>
                                                                                    <option value="BR">Brazil</option>
                                                                                    <option value="BN">Brunei Darussalam</option>
                                                                                    <option value="BG">Bulgaria</option>
                                                                                    <option value="BF">Burkina Faso</option>
                                                                                    <option value="BI">Burundi</option>
                                                                                    <option value="KH">Cambodia</option>
                                                                                    <option value="CM">Cameroon</option>
                                                                                    <option value="CA">Canada</option>
                                                                                    <option value="CV">Cape Verde</option>
                                                                                    <option value="KY">Cayman Islands</option>
                                                                                    <option value="CF">Central African Republic</option>
                                                                                    <option value="TD">Chad</option>
                                                                                    <option value="CL">Chile</option>
                                                                                    <option value="CN">China</option>
                                                                                    <option value="CX">Christmas Island</option>
                                                                                    <option value="CC">Cocos (Keeling) Islands</option>
                                                                                    <option value="CO">Colombia</option>
                                                                                    <option value="KM">Comoros</option>
                                                                                    <option value="CG">Congo</option>
                                                                                    <option value="CK">Cook Islands</option>
                                                                                    <option value="CR">Costa Rica</option>
                                                                                    <option value="CI">Cote d'Ivoire</option>
                                                                                    <option value="HR">Croatia (Hrvatska)</option>
                                                                                    <option value="CU">Cuba</option>
                                                                                    <option value="CY">Cyprus</option>
                                                                                    <option value="CZ">Czech Republic</option>
                                                                                    <option value="DK">Denmark</option>
                                                                                    <option value="DJ">Djibouti</option>
                                                                                    <option value="DM">Dominica</option>
                                                                                    <option value="DO">Dominican Republic</option>
                                                                                    <option value="EC">Ecuador</option>
                                                                                    <option value="EG">Egypt</option>
                                                                                    <option value="SV">El Salvador</option>
                                                                                    <option value="GQ">Equatorial Guinea</option>
                                                                                    <option value="ER">Eritrea</option>
                                                                                    <option value="EE">Estonia</option>
                                                                                    <option value="ET">Ethiopia</option>
                                                                                    <option value="FK">Falkland Islands (Malvinas)</option>
                                                                                    <option value="FO">Faroe Islands</option>
                                                                                    <option value="FJ">Fiji</option>
                                                                                    <option value="FI">Finland</option>
                                                                                    <option value="FR">France</option>
                                                                                    <option value="GF">French Guiana</option>
                                                                                    <option value="PF">French Polynesia</option>
                                                                                    <option value="GA">Gabon</option>
                                                                                    <option value="GM">Gambia</option>
                                                                                    <option value="GE">Georgia</option>
                                                                                    <option value="DE">Germany</option>
                                                                                    <option value="GH">Ghana</option>
                                                                                    <option value="GI">Gibraltar</option>
                                                                                    <option value="GR">Greece</option>
                                                                                    <option value="GL">Greenland</option>
                                                                                    <option value="GD">Grenada</option>
                                                                                    <option value="GP">Guadeloupe</option>
                                                                                    <option value="GU">Guam</option>
                                                                                    <option value="GT">Guatemala</option>
                                                                                    <option value="GN">Guinea</option>
                                                                                    <option value="GW">Guinea-Bissau</option>
                                                                                    <option value="GY">Guyana</option>
                                                                                    <option value="HT">Haiti</option>
                                                                                    <option value="HN">Honduras</option>
                                                                                    <option value="HK">Hong Kong</option>
                                                                                    <option value="HU">Hungary</option>
                                                                                    <option value="IS">Iceland</option>
                                                                                    <option value="IN">India</option>
                                                                                    <option value="ID">Indonesia</option>
                                                                                    <option value="IQ">Iraq</option>
                                                                                    <option value="IE">Ireland</option>
                                                                                    <option value="IL">Israel</option>
                                                                                    <option value="IT">Italy</option>
                                                                                    <option value="JM">Jamaica</option>
                                                                                    <option value="JP">Japan</option>
                                                                                    <option value="JO">Jordan</option>
                                                                                    <option value="KZ">Kazakhstan</option>
                                                                                    <option value="KE">Kenya</option>
                                                                                    <option value="KI">Kiribati</option>
                                                                                    <option value="KR">Korea, Republic of</option>
                                                                                    <option value="KW">Kuwait</option>
                                                                                    <option value="KG">Kyrgyzstan</option>
                                                                                    <option value="LV">Latvia</option>
                                                                                    <option value="LB">Lebanon</option>
                                                                                    <option value="LS">Lesotho</option>
                                                                                    <option value="LR">Liberia</option>
                                                                                    <option value="LY">Libyan Arab Jamahiriya</option>
                                                                                    <option value="LI">Liechtenstein</option>
                                                                                    <option value="LT">Lithuania</option>
                                                                                    <option value="LU">Luxembourg</option>
                                                                                    <option value="MO">Macau</option>
                                                                                    <option value="MG">Madagascar</option>
                                                                                    <option value="MW">Malawi</option>
                                                                                    <option value="MY">Malaysia</option>
                                                                                    <option value="MV">Maldives</option>
                                                                                    <option value="ML">Mali</option>
                                                                                    <option value="MT">Malta</option>
                                                                                    <option value="MH">Marshall Islands</option>
                                                                                    <option value="MQ">Martinique</option>
                                                                                    <option value="MR">Mauritania</option>
                                                                                    <option value="MU">Mauritius</option>
                                                                                    <option value="YT">Mayotte</option>
                                                                                    <option value="MX">Mexico</option>
                                                                                    <option value="MD">Moldova, Republic of</option>
                                                                                    <option value="MC">Monaco</option>
                                                                                    <option value="MN">Mongolia</option>
                                                                                    <option value="MS">Montserrat</option>
                                                                                    <option value="MA">Morocco</option>
                                                                                    <option value="MZ">Mozambique</option>
                                                                                    <option value="MM">Myanmar</option>
                                                                                    <option value="NA">Namibia</option>
                                                                                    <option value="NR">Nauru</option>
                                                                                    <option value="NP">Nepal</option>
                                                                                    <option value="NL">Netherlands</option>
                                                                                    <option value="AN">Netherlands Antilles</option>
                                                                                    <option value="NC">New Caledonia</option>
                                                                                    <option value="NZ">New Zealand</option>
                                                                                    <option value="NI">Nicaragua</option>
                                                                                    <option value="NE">Niger</option>
                                                                                    <option value="NG">Nigeria</option>
                                                                                    <option value="NU">Niue</option>
                                                                                    <option value="NF">Norfolk Island</option>
                                                                                    <option value="MP">Northern Mariana Islands</option>
                                                                                    <option value="NO">Norway</option>
                                                                                    <option value="OM">Oman</option>
                                                                                    <option value="PW">Palau</option>
                                                                                    <option value="PA">Panama</option>
                                                                                    <option value="PG">Papua New Guinea</option>
                                                                                    <option value="PY">Paraguay</option>
                                                                                    <option value="PE">Peru</option>
                                                                                    <option value="PH">Philippines</option>
                                                                                    <option value="PN">Pitcairn</option>
                                                                                    <option value="PL">Poland</option>
                                                                                    <option value="PT">Portugal</option>
                                                                                    <option value="PR">Puerto Rico</option>
                                                                                    <option value="QA">Qatar</option>
                                                                                    <option value="RE">Reunion</option>
                                                                                    <option value="RO">Romania</option>
                                                                                    <option value="RU">Russian Federation</option>
                                                                                    <option value="RW">Rwanda</option>
                                                                                    <option value="KN">Saint Kitts and Nevis</option>
                                                                                    <option value="LC">Saint LUCIA</option>
                                                                                    <option value="WS">Samoa</option>
                                                                                    <option value="SM">San Marino</option>
                                                                                    <option value="ST">Sao Tome and Principe</option>
                                                                                    <option value="SA">Saudi Arabia</option>
                                                                                    <option value="SN">Senegal</option>
                                                                                    <option value="SC">Seychelles</option>
                                                                                    <option value="SL">Sierra Leone</option>
                                                                                    <option value="SG">Singapore</option>
                                                                                    <option value="SK">Slovakia (Slovak Republic)</option>
                                                                                    <option value="SI">Slovenia</option>
                                                                                    <option value="SB">Solomon Islands</option>
                                                                                    <option value="SO">Somalia</option>
                                                                                    <option value="ZA">South Africa</option>
                                                                                    <option value="ES">Spain</option>
                                                                                    <option value="LK">Sri Lanka</option>
                                                                                    <option value="SH">St. Helena</option>
                                                                                    <option value="PM">St. Pierre and Miquelon</option>
                                                                                    <option value="SD">Sudan</option>
                                                                                    <option value="SR">Suriname</option>
                                                                                    <option value="SZ">Swaziland</option>
                                                                                    <option value="SE">Sweden</option>
                                                                                    <option value="CH">Switzerland</option>
                                                                                    <option value="SY">Syrian Arab Republic</option>
                                                                                    <option value="TW">Taiwan, Province of China</option>
                                                                                    <option value="TJ">Tajikistan</option>
                                                                                    <option value="TZ">Tanzania, United Republic of</option>
                                                                                    <option value="TH">Thailand</option>
                                                                                    <option value="TG">Togo</option>
                                                                                    <option value="TK">Tokelau</option>
                                                                                    <option value="TO">Tonga</option>
                                                                                    <option value="TT">Trinidad and Tobago</option>
                                                                                    <option value="TN">Tunisia</option>
                                                                                    <option value="TR">Turkey</option>
                                                                                    <option value="TM">Turkmenistan</option>
                                                                                    <option value="TC">Turks and Caicos Islands</option>
                                                                                    <option value="TV">Tuvalu</option>
                                                                                    <option value="UG">Uganda</option>
                                                                                    <option value="UA">Ukraine</option>
                                                                                    <option value="AE">United Arab Emirates</option>
                                                                                    <option value="GB">United Kingdom</option>
                                                                                    <option value="US">United States</option>
                                                                                    <option value="UY">Uruguay</option>
                                                                                    <option value="UZ">Uzbekistan</option>
                                                                                    <option value="VU">Vanuatu</option>
                                                                                    <option value="VE">Venezuela</option>
                                                                                    <option value="VN">Viet Nam</option>
                                                                                    <option value="VG">Virgin Islands (British)</option>
                                                                                    <option value="VI">Virgin Islands (U.S.)</option>
                                                                                    <option value="WF">Wallis and Futuna Islands</option>
                                                                                    <option value="EH">Western Sahara</option>
                                                                                    <option value="YE">Yemen</option>
                                                                                    <option value="ZM">Zambia</option>
                                                                                    <option value="ZW">Zimbabwe</option>                                    
                                                                                </select>
                                                                            </div>
                                                                        </div>
            
                                                                        <div class="col-lg-4">
                                                                            <div class="mb-4 mb-lg-0">
                                                                                <label class="form-label" for="billing-city">City</label>
                                                                                <input type="text" class="form-control" id="billing-city" placeholder="Enter City">
                                                                            </div>
                                                                        </div>
            
                                                                        <div class="col-lg-4">
                                                                            <div class="mb-0">
                                                                                <label class="form-label" for="zip-code">Zip / Postal code</label>
                                                                                <input type="text" class="form-control" id="zip-code" placeholder="Enter Postal code">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                            <li class="checkout-item">
                                                <div class="avatar checkout-icon p-1">
                                                    <div class="avatar-title rounded-circle bg-primary">
                                                        <h5 class="text-white font-size-16 mb-0">02</h5>
                                                    </div>
                                                </div>
                                                <div class="feed-item-list">
                                                    <div>
                                                        <h5 class="font-size-16 mb-1">Shipping Info</h5>
                                                        <p class="text-muted text-truncate mb-4">Neque porro quisquam est</p>
                                                        <div class="mb-3">
                                                            <div class="row">
                                                                <div class="col-lg-4 col-sm-6">
                                                                    <div data-bs-toggle="collapse">
                                                                        <label class="card-radio-label mb-0">
                                                                            <input type="radio" name="address" id="info-address1" class="card-radio-input" checked>
                                                                            <div class="card-radio text-truncate p-3">
                                                                                <span class="fs-14 mb-4 d-block">Address 1</span>
                                                                                <span class="fs-14 mb-2 d-block">Bradley McMillian</span>
                                                                                <span class="text-muted fw-normal text-wrap mb-1 d-block">109 Clarksburg Park Road Show Low, AZ 85901</span>
                                                                               
                                                                                <span class="text-muted fw-normal d-block">Mo. 012-345-6789</span>
                                                                            </div>
                                                                        </label>
                                                                        <div class="edit-btn bg-light  rounded">
                                                                            <a href="#"  data-bs-toggle="tooltip" data-placement="top" title="" data-bs-original-title="Edit">
                                                                                <i class="bx bx-pencil font-size-16"></i>
                                                                            </a>
                                                                        </div>
                                                                    </div>
                                                                </div>
            
                                                                <div class="col-lg-4 col-sm-6">
                                                                    <div>
                                                                        <label class="card-radio-label mb-0">
                                                                            <input type="radio" name="address" id="info-address2" class="card-radio-input">
                                                                            <div class="card-radio text-truncate p-3">
                                                                                <span class="fs-14 mb-4 d-block">Address 2</span>
                                                                                <span class="fs-14 mb-2 d-block">Bradley McMillian</span>
                                                                                <span class="text-muted fw-normal text-wrap mb-1 d-block">109 Clarksburg Park Road Show Low, AZ 85901</span>
                                                                                <span class="text-muted fw-normal d-block">Mo. 012-345-6789</span>
                                                                            </div>
                                                                        </label>
                                                                        <div class="edit-btn bg-light  rounded">
                                                                            <a href="#"  data-bs-toggle="tooltip" data-placement="top" title="" data-bs-original-title="Edit">
                                                                                <i class="bx bx-pencil font-size-16"></i>
                                                                            </a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                            <li class="checkout-item pb-2">
                                                <div class="avatar checkout-icon p-1">
                                                    <div class="avatar-title rounded-circle bg-primary">
                                                        <h5 class="text-white font-size-16 mb-0">03</h5>
                                                    </div>
                                                </div>
                                                <div class="feed-item-list">
                                                    <div>
                                                        <h5 class="font-size-16 mb-1">Payment Info</h5>
                                                        <p class="text-muted text-truncate mb-4">Duis arcu tortor, suscipit eget</p>
                                                    </div>
                                                    <div>
                                                        <h5 class="font-size-14 mb-3">Payment method :</h5>
                                                        <div class="row">
                                                            <div class="col-lg-3 col-sm-6">
                                                                <div data-bs-toggle="collapse">
                                                                    <label class="card-radio-label">
                                                                        <input type="radio" name="pay-method" id="pay-methodoption1" class="card-radio-input">
                                                                        <span class="card-radio py-3 text-center text-truncate">
                                                                            <i class="bx bx-credit-card d-block h2 mb-3"></i>
                                                                            Credit / Debit Card
                                                                        </span>
                                                                    </label>
                                                                </div>
                                                            </div>
                                                            
                                                            <div class="col-lg-3 col-sm-6">
                                                                <div>
                                                                    <label class="card-radio-label">
                                                                        <input type="radio" name="pay-method" id="pay-methodoption2" class="card-radio-input">
                                                                        <span class="card-radio py-3 text-center text-truncate">
                                                                            <i class="bx bxl-paypal d-block h2 mb-3"></i>
                                                                            Paypal
                                                                        </span>
                                                                    </label>
                                                                </div>
                                                            </div>
    
                                                            <div class="col-lg-3 col-sm-6">
                                                                <div>
                                                                    <label class="card-radio-label">
                                                                        <input type="radio" name="pay-method" id="pay-methodoption3" class="card-radio-input" checked>
            
                                                                        <span class="card-radio py-3 text-center text-truncate">
                                                                            <i class="bx bx-money d-block h2 mb-3"></i>
                                                                            <span>Cash on Delivery</span>
                                                                        </span>
                                                                    </label>
                                                                </div>
                                                            </div>
                                                            
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                        </ol>
                                    </div>
                                </div>

                                <div class="row my-4">
                                    <div class="col">
                                        <a href="/ecommerce-products" class="btn btn-link text-muted">
                                            <i class="mdi mdi-arrow-left me-1"></i> Continue Shopping </a>
                                    </div> <!-- end col -->
                                    <div class="col">
                                        <div class="text-end mt-2 mt-sm-0">
                                            <a href="#" class="btn btn-success">
                                                <i class="mdi mdi-cart-outline me-1"></i> Procced </a>
                                        </div>
                                    </div> <!-- end col -->
                                </div> <!-- end row-->
                            </div>
                            <div class="col-xl-4">
                                <div class="card checkout-order-summary">
                                    <div class="card-body">
                                        <div class="p-3 bg-light mb-3">
                                            <h5 class="font-size-16 mb-0">Order Summary <span class="float-end ms-2">#MN0124</span></h5>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table align-middle mb-0 table-nowrap">
                                                <thead>
                                                    <tr>
                                                        <th class="border-top-0" style="width: 90px;" scope="col">Product</th>
                                                        <th class="border-top-0" scope="col">Product Desc</th>
                                                        <th class="border-top-0" scope="col">Price</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse (($items ?? collect()) as $ci)
                                                    <tr>
                                                        <th scope="row"><img src="{{ $ci->product?->image ?? 'assets/images/product/img-1.png' }}" alt="product-img" title="product-img" class="avatar-md"></th>
                                                        <td>
                                                            <h5 class="font-size-15 text-truncate"><a href="/ecommerce-product-detail" class="text-body">{{ $ci->product?->name ?? 'Product' }}</a></h5>
                                                            <p class="text-muted mb-0">$ {{ number_format((float) $ci->price, 2) }} x {{ (int) $ci->quantity }}</p>
                                                        </td>
                                                        <td>$ {{ number_format((float) ($ci->price * $ci->quantity), 2) }}</td>
                                                    </tr>
                                                    @empty
                                                    <tr>
                                                        <td colspan="3" class="text-center text-muted">Your cart is empty.</td>
                                                    </tr>
                                                    @endforelse
                                                    @php
                                                        $coShip = (float) ($shipping ?? 0);
                                                        $coTax = (float) ($tax ?? 0);
                                                        $coSub = (float) ($subtotal ?? 0);
                                                        $coTotal = $coSub + $coTax + $coShip;
                                                    @endphp
                                                    <tr>
                                                        <td colspan="2">
                                                            <h5 class="font-size-14 m-0">Sub Total :</h5>
                                                        </td>
                                                        <td>
                                                            $ {{ number_format($coSub, 2) }}
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="2">
                                                            <h5 class="font-size-14 m-0">Discount :</h5>
                                                        </td>
                                                        <td>
                                                            - $ 0.00
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <td colspan="2">
                                                            <h5 class="font-size-14 m-0">Shipping Charge :</h5>
                                                        </td>
                                                        <td>
                                                            $ {{ number_format($coShip, 2) }}
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="2">
                                                            <h5 class="font-size-14 m-0">Estimated Tax :</h5>
                                                        </td>
                                                        <td>
                                                            $ {{ number_format($coTax, 2) }}
                                                        </td>
                                                    </tr>

                                                    <tr class="bg-light">
                                                        <td colspan="2">
                                                            <h5 class="font-size-14 m-0">Total:</h5>
                                                        </td>
                                                        <td>
                                                            $ {{ number_format($coTotal, 2) }}
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- end row -->

                    </div>
                    <!-- container-fluid -->
                </div>
                <!-- End Page-content -->

                <footer class="footer">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-sm-6">
                                <script>document.write(new Date().getFullYear())</script> © Cyprus Digital Solution.
                            </div>
                            <div class="col-sm-6">
                                <div class="text-sm-end d-none d-sm-block">
                                    Crafted with <i class="mdi mdi-heart text-danger"></i> by <a href="https://themesdesign.in/" target="_blank" class="text-reset">Themesdesign</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </footer>
            </div>
            <!-- end main content-->

        </div>
        <!-- END layout-wrapper -->

        <!-- Right Sidebar -->
        <a href="#" class="right-bar-toggle layout-setting-btn" id="right-bar-toggle">
            <i class="bx bx-cog icon-sm font-size-18"></i> <span>Settings</span>
        </a>

        <!-- Right Sidebar -->
        <div class="right-bar">
            <div data-simplebar class="h-100">
                <div class="rightbar-title d-flex align-items-center bg-dark p-3">

                    <h5 class="m-0 me-2 text-white">Theme Customizer</h5>

                    <a href="javascript:void(0);" class="right-bar-toggle-close ms-auto">
                        <i class="mdi mdi-close noti-icon"></i>
                    </a>
                </div>

                <!-- Settings -->
                <hr class="m-0" />

                <div class="p-4">
                    <h6 class="mb-3">Layout</h6>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="layout"
                            id="layout-vertical" value="vertical">
                        <label class="form-check-label" for="layout-vertical">Vertical</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="layout"
                            id="layout-horizontal" value="horizontal">
                        <label class="form-check-label" for="layout-horizontal">Horizontal</label>
                    </div>

                    <h6 class="mt-4 mb-3">Layout Mode</h6>

                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="layout-mode"
                            id="layout-mode-light" value="light">
                        <label class="form-check-label" for="layout-mode-light">Light</label>
                    </div>

                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="layout-mode"
                            id="layout-mode-dark" value="dark">
                        <label class="form-check-label" for="layout-mode-dark">Dark</label>
                    </div>

                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="layout-mode"
                            id="layout-mode-bordered" value="bordered">
                        <label class="form-check-label" for="layout-mode-bordered">Bordered</label>
                    </div>

                    <h6 class="mt-4 mb-3">Layout Width</h6>

                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="layout-width"
                            id="layout-width-fluid" value="fluid" onchange="document.body.setAttribute('data-layout-size', 'fluid')">
                        <label class="form-check-label" for="layout-width-fluid">Fluid</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="layout-width"
                            id="layout-width-boxed" value="boxed" onchange="document.body.setAttribute('data-layout-size', 'boxed')">
                        <label class="form-check-label" for="layout-width-boxed">Boxed</label>
                    </div>

                    <h6 class="mt-4 mb-3">Layout Position</h6>

                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="layout-position"
                            id="layout-position-fixed" value="fixed" onchange="document.body.setAttribute('data-layout-scrollable', 'false')">
                        <label class="form-check-label" for="layout-position-fixed">Fixed</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="layout-position"
                            id="layout-position-scrollable" value="scrollable" onchange="document.body.setAttribute('data-layout-scrollable', 'true')">
                        <label class="form-check-label" for="layout-position-scrollable">Scrollable</label>
                    </div>

                    <h6 class="mt-4 mb-3">Topbar Type</h6>

            
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="topbar-color"
                            id="topbar-color-light" value="light" onchange="document.body.setAttribute('data-topbar', 'light')">
                        <label class="form-check-label" for="topbar-color-light">Light</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="topbar-color"
                            id="topbar-color-dark" value="dark" onchange="document.body.setAttribute('data-topbar', 'dark')">
                        <label class="form-check-label" for="topbar-color-dark">Dark</label>
                    </div>

                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="topbar-color"
                            id="topbar-type-hidden" value="hidden" onchange="document.body.setAttribute('data-topbar', 'hidden')">
                        <label class="form-check-label" for="topbar-type-hidden">Hidden</label>
                    </div>
            

                    <div id="sidebar-setting">
                    <h6 class="mt-4 mb-3 sidebar-setting">Sidebar Size</h6>

                    <div class="form-check sidebar-setting">
                        <input class="form-check-input" type="radio" name="sidebar-size"
                            id="sidebar-size-default" value="default" onchange="document.body.setAttribute('data-sidebar-size', 'lg')">
                        <label class="form-check-label" for="sidebar-size-default">Default</label>
                    </div>
                    <div class="form-check sidebar-setting">
                        <input class="form-check-input" type="radio" name="sidebar-size"
                            id="sidebar-size-compact" value="compact" onchange="document.body.setAttribute('data-sidebar-size', 'md')">
                        <label class="form-check-label" for="sidebar-size-compact">Compact</label>
                    </div>
                    <div class="form-check sidebar-setting">
                        <input class="form-check-input" type="radio" name="sidebar-size"
                            id="sidebar-size-small" value="small" onchange="document.body.setAttribute('data-sidebar-size', 'sm')">
                        <label class="form-check-label" for="sidebar-size-small">Small (Icon View)</label>
                    </div>

                    <h6 class="mt-4 mb-3 sidebar-setting">Sidebar Color</h6>

                    <div class="form-check sidebar-setting">
                        <input class="form-check-input" type="radio" name="sidebar-color"
                            id="sidebar-color-light" value="light" onchange="document.body.setAttribute('data-sidebar', 'light')">
                        <label class="form-check-label" for="sidebar-color-light">Light</label>
                    </div>
                    <div class="form-check sidebar-setting">
                        <input class="form-check-input" type="radio" name="sidebar-color"
                            id="sidebar-color-dark" value="dark" onchange="document.body.setAttribute('data-sidebar', 'dark')">
                        <label class="form-check-label" for="sidebar-color-dark">Dark</label>
                    </div>
                    <div class="form-check sidebar-setting">
                        <input class="form-check-input" type="radio" name="sidebar-color"
                            id="sidebar-color-brand" value="brand" onchange="document.body.setAttribute('data-sidebar', 'brand')">
                        <label class="form-check-label" for="sidebar-color-brand">Brand</label>
                    </div>
                </div>

                    <h6 class="mt-4 mb-3">Direction</h6>

                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="layout-direction"
                            id="layout-direction-ltr" value="ltr">
                        <label class="form-check-label" for="layout-direction-ltr">LTR</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="layout-direction"
                            id="layout-direction-rtl" value="rtl">
                        <label class="form-check-label" for="layout-direction-rtl">RTL</label>
                    </div>

                </div>

            </div> <!-- end slimscroll-menu-->
        </div>
        <!-- /Right-bar -->

        <!-- Right bar overlay-->
        <div class="rightbar-overlay"></div>

        <!-- chat offcanvas -->
        <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasActivity" aria-labelledby="offcanvasActivityLabel">
            <div class="offcanvas-header border-bottom">
              <h5 id="offcanvasActivityLabel">Offcanvas right</h5>
              <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
              ...
            </div>
        </div>

        <!-- JAVASCRIPT -->
        <script src="assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
        <script src="assets/libs/metismenujs/metismenujs.min.js"></script>
        <script src="assets/libs/simplebar/simplebar.min.js"></script>
        <script src="assets/libs/eva-icons/eva.min.js"></script>

        <script src="assets/js/app.js"></script>

    </body>

</html>
