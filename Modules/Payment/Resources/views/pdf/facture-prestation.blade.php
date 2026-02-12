<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Facture prestation(s)</title>

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

    <style>
        body {
            padding: 0;
            margin: 0;
            /* Supprime les marges par défaut du corps du document */
            font-family: var(--bs-font-sans-serif);
            font-size: 1rem;
            font-weight: 400;
            line-height: 1.5;
            color: #212529;
            background-color: #fff;
            -webkit-text-size-adjust: 100%;
            -webkit-tap-highlight-color: rgba(0, 0, 0, 0);

        }

        .table-bordered {
            border: 1px solid #b6b9bb;
        }

        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }


        table {
            caption-side: bottom;
            border-collapse: collapse;
            margin-bottom: 1rem;
            width: 100%;
            max-width: 100%;
            border-collapse: collapse;
            font-family: 'Arial' !important;
        }


        table th {
            background-color: #d6dbd6;
            border: 1px solid #d2d4d4;
            text-align: left;
            padding: 0.4rem !important;
        }

        .table th,
        .table td {
            padding: 0.2em 0.6em;
        }

        table td,
        table th {
            border: 1px solid #d2d4d4;
            padding: 0.4rem;
            vertical-align: top;
            border-top: 1px solid #dee2e6;
            font-family: "Montserrat" !important;
        }



        hr {
            background-color: #fcfdff;
            /* background-color: white; */
            margin-top: 0px;
        }



        .flex-container {
            display: flex;
            justify-content: space-between;
            /* Distribue l'espace entre les éléments */

            width: 200%;
            /* Assurez-vous que le conteneur occupe toute la largeur */
        }



        .row-margin {
            margin-top: 20px;
            /* Ajoute une marge en haut de la ligne */
        }


        .mb-2 {
            margin-bottom: 0.5rem !important;
        }

        .row {
            --bs-gutter-x: 1.5rem;
            --bs-gutter-y: 0;
            display: flex;
            flex-wrap: wrap;
            margin-top: calc(var(--bs-gutter-y) * -1);
            margin-right: calc(var(--bs-gutter-x) * -.5);
            margin-left: calc(var(--bs-gutter-x) * -.5);
        }



        .container,
        .container-fluid,
        .container-xxl,
        .container-xl,
        .container-lg,
        .container-md,
        .container-sm {
            width: 100%;
            padding-right: var(--bs-gutter-x, 0.75rem);
            padding-left: var(--bs-gutter-x, 0.75rem);
            margin-right: auto;
            margin-left: auto;
        }

        .col-sm-12 {
            flex: 0 0 auto !important;
            width: 100% !important;
        }

        .d-flex {
            display: flex !important;
        }

        .justify-content-between {
            justify-content: space-between !important;
        }

        .image-container {
            display: flex;
            width: 100vw;
            justify-content: space-between;
            /* padding: 0;
            margin: 0; */
        }



        .text-start {
            text-align: left !important;
        }

        .text-left {
            text-align: left !important;
        }

        .text-right {
            text-align: right !important;
        }

        table td,
        table th {
            padding: 0.1rem !important;
            /* vertical-align: top; */

        }


        .text-end {
            text-align: right !important;
        }

        .text-center {
            text-align: center !important;
        }

        .col-sm-6 {
            flex: 0 0 auto;
            width: 50%;
        }

        .col-md-12 {
            flex: 0 0 auto;
            width: 100%;
        }

        .col-md-6 {
            flex: 0 0 auto;
            width: 50%;
        }

        .bordered-container-red {
            border: 5px solid red;
            border-radius: 15px;
            display: inline-block;
            padding: 2px;
            transform: rotate(-35deg);
        }

        .bordered-container-green {
            border: 5px solid green;
            border-radius: 15px;
            display: inline-block;
            padding: 2px;
            transform: rotate(-35deg);
        }
    </style>

</head>

<body>

    <div class="container-fluid row">

        <div class="image-container  col-sm-12">
            <img src="{{ public_path('images/logo.png') }}" alt="logo" class="logo-image"
                style="width: 10% !important; height:  6% !important; " />
            <img src="{{ public_path('images/santee.png') }}" alt="logo" class="santee-image text-right"
                style="width:20% !important; height:5% !important; margin-left: 69%" />
        </div>

        <hr style="background-color: #cacdcf !important; margin-top: -4px;  border: 1px solid #cacdcf !important;">

        <div class="row col-sm-12  ">
            <div class="col-sm-6 text-left ">
                <h4>PARTIE VERSANTE :</h4>
                <p style="margin-top: -8px !important;"><strong>{{ $patientInfo->lastname }}
                        {{ $patientInfo->firstname }}</strong></p>
                <p style="margin-top: -10px !important;">{{ $patientInfo->maison }}</p>
                <p style="margin-top: -10px !important;">{{ $patientInfo->phone }}</p>
                <p style="margin-top: -10px !important;"><strong>IPP :</strong> {{ $patientInfo->ipp }} | <strong>IEP :
                        {{ $patientInfo->iep }} </strong></p>
            </div>
            <div class="col-sm-6 text-right" style="margin-left: 50%; margin-top: -50%">
                @if (count($facturesTypeA) > 0)
                    <h4>FACTURE N° {{ $facturesTypeA[0]['reference'] }}</h4>
                    <p style="margin-top: -10px !important;"><strong>Date :
                            {{ formatDate($facturesTypeA[0]['created_at']) }} </strong></p>
                @endif
                <p style="margin-top: -10px !important; font-size: 14px;"><strong>LOKOSSA ATHIEME</strong></p>
                <p style="margin-top: -10px !important;">Code postal 09 6 51 03</p>
                <p style="margin-top: -10px !important;">En allant à LINK Hotel</p>
            </div>
        </div>



        <div class="table-responsive" style="margin-top: 2%;">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Désignation</th>
                        <th style="text-align: center">Quantité</th>
                        <th style="text-align: right">Prix unitaire</th>
                        <th style="text-align: right">Montant</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $total = 0; // Initialisez la variable totale
                    @endphp

                    @foreach ($facturesTypeA as $facture)
                        <tr>
                            <td>{{ $facture['code'] }}</td>
                            <td>{{ $facture['designation'] }}</td>
                            <td style="text-align: center">{{ $facture['quantite'] }}</td>
                            <td style="text-align: right">{{ format_number($facture['prix']) }}</td>
                            <td style="text-align: right">{{ format_number($facture['amount']) }}</td>
                            @php
                                $total += $facture['amount']; // Mettez à jour le total
                            @endphp
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" class="text-left">
                            <strong>Montant total (+) </strong>
                        </td>
                        <td style="text-align: right">
                            <strong>{{ format_number($total) }} FCFA</strong>
                        </td>
                    </tr>

                    <tr>
                        <td colspan="4" class="text-left">
                            <strong> Montant de la prise en charge (-)</strong>
                        </td>
                        <td style="text-align: right;">
                            @php
                                $montantPrisenchargePourcentage = 0;
                                
                                if (count($facturesTypeA) > 0) {
                                    $montantPrisenchargePourcentage = ($total * $facturesTypeA[0]['percentageassurance'] ) / 100;
                                }
                            @endphp
                            <strong>{{ format_number($montantPrisenchargePourcentage) }} FCFA</strong>
                        </td>
                    </tr>

                    <tr>
                        <td colspan="4" class="text-left" style="background-color: rgb(213, 219, 218)">
                            <strong>Montant total versé</strong>
                        </td>
                        <td style="background-color: rgb(213, 219, 218); text-align: right ">
                            <strong>{{ format_number($total - $montantPrisenchargePourcentage) }} FCFA</strong>
                        </td>
                    </tr>
                </tfoot>
            </table>


            <div class="row row-margin">
                <div class="col-sm-8">

                    {{-- @if (isset($facturesTypeA[0]['mode_payements_id']) && count($facturesTypeA) > 0)


                    <p style="font-size: 14px">
                        <strong style="text-transform: capitalize">
                            MODE DE PAIEMENT :
                            @if ($facturesTypeA[0]['mode_payements_id'] == 1)
                            ESPECE
                            @elseif($facturesTypeA[0]['mode_payements_id'] == 2)
                            MTN MOBILE MONEY
                            @elseif($facturesTypeA[0]['mode_payements_id'] == 3)
                            MOOV MONEY (FLOOZ)
                            @elseif($facturesTypeA[0]['mode_payements_id'] == 4)
                            CELTIS
                            @elseif($facturesTypeA[0]['mode_payements_id'] == 5)
                            CARTE BANCAIRE
                            @elseif($facturesTypeA[0]['mode_payements_id'] == 6)
                            CARTE DE CRÉDIT
                            @elseif($facturesTypeA[0]['mode_payements_id'] == 7)
                            PAIEMENT TRESORPAY
                            @elseif($facturesTypeA[0]['mode_payements_id'] == 8)
                            PAIEMENT A CREDIT
                            @endif
                        </strong>
                    </p>
                    @endif --}}
                    <p>
                        Arrêté la présente facture à la somme de :
                        <br>
                        <strong style="text-transform: capitalize; font-size: 15px">{{ format_letter($total - $montantPrisenchargePourcentage) }}
                            FCFA</strong>
                    </p>


                    <p>
                        <a href="{{ asset('storage/pdf/facture_prestation_' . $codeUnique . '.pdf') }}"
                            target="_blank">
                            <img src="data:image/png;base64,{{ base64_encode($qrCode) }}" alt="Code QR">
                        </a>
                    </p>

                    <!-- <div>
                        @if (count($facturesTypeA) > 0)
                        @if ($facturesTypeA[0]['paid'] == 0)
<h4 class="bordered-container-red" style="font-family: Impact !important; color: red; font-weight: bold; font-size: 15px; letter-spacing: 4px; padding: 5px; display: inline-block;">
                            NON PAYÉE
                        </h4>
@elseif($facturesTypeA[0]['paid'] == 1)
<h4 class="bordered-container-green" style="font-family: Impact !important; color: green; font-weight: bold; font-size: 15px; letter-spacing: 4px; padding: 5px; display: inline-block;">
                            PAYÉE
                        </h4>
@endif
                        @endif
                    </div> -->
                </div>
                <div class="col-sm-4 text-right " style="margin-left: 50%; margin-top: -50%">
                    <p><strong> Le Caissier </strong></p>
                    <img src="{{ public_path('images/signature.png') }}" alt="logo" class=""
                        style="width: 80px" />

                    <br>
                    <p><strong> Félicien DAGBOGBO </strong></p>
                </div>
            </div>
        </div>
    </div>





    <script src="{{ public_path('assets/bootstrap-5.0.2/js/bootstrap.bundle.min.js') }}"></script>

</body>

</html>
