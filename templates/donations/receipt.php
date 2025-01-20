<?php
use Core\View;
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reçu de don - <?= $donation['id'] ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 14px;
        }
        .receipt {
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid #ddd;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .church-info {
            margin-bottom: 30px;
        }
        .donation-info {
            margin-bottom: 30px;
        }
        .donor-info {
            margin-bottom: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #f5f5f5;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
        }
        .signature {
            margin-top: 50px;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
        @media print {
            body {
                padding: 0;
            }
            .receipt {
                border: none;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="header">
            <h1>Reçu de don</h1>
            <p>N° <?= str_pad($donation['id'], 6, '0', STR_PAD_LEFT) ?></p>
        </div>

        <div class="church-info">
            <h3><?= $church['name'] ?></h3>
            <p><?= $church['address'] ?></p>
            <p>Tél : <?= $church['phone'] ?></p>
            <p>Email : <?= $church['email'] ?></p>
        </div>

        <div class="donor-info">
            <h3>Informations du donateur</h3>
            <p><strong>Nom :</strong> <?= $donation['member_name'] ?? 'Anonyme' ?></p>
            <?php if (isset($donation['member_address'])): ?>
            <p><strong>Adresse :</strong> <?= $donation['member_address'] ?></p>
            <?php endif; ?>
        </div>

        <div class="donation-info">
            <h3>Détails du don</h3>
            <table>
                <tr>
                    <th>Date</th>
                    <td><?= date('d/m/Y', strtotime($donation['date'])) ?></td>
                </tr>
                <tr>
                    <th>Type de don</th>
                    <td>
                        <?php
                        $types = [
                            'tithe' => 'Dîme',
                            'offering' => 'Offrande',
                            'special' => 'Don spécial',
                            'project' => 'Projet'
                        ];
                        echo $types[$donation['type']] ?? $donation['type'];
                        ?>
                    </td>
                </tr>
                <tr>
                    <th>Montant</th>
                    <td><?= number_format($donation['amount'], 0, ',', ' ') ?> XOF</td>
                </tr>
                <tr>
                    <th>Mode de paiement</th>
                    <td>
                        <?php
                        $methods = [
                            'cash' => 'Espèces',
                            'check' => 'Chèque',
                            'bank_transfer' => 'Virement bancaire',
                            'mobile_money' => 'Mobile Money',
                            'other' => 'Autre'
                        ];
                        echo $methods[$donation['payment_method']] ?? $donation['payment_method'];
                        ?>
                    </td>
                </tr>
                <?php if ($donation['reference_number']): ?>
                <tr>
                    <th>Référence</th>
                    <td><?= $donation['reference_number'] ?></td>
                </tr>
                <?php endif; ?>
                <?php if ($donation['campaign']): ?>
                <tr>
                    <th>Campagne</th>
                    <td><?= $donation['campaign'] ?></td>
                </tr>
                <?php endif; ?>
            </table>
        </div>

        <div class="signature">
            <div style="float: left; width: 45%;">
                <p>Signature du donateur</p>
                <br><br><br>
                _______________________
            </div>
            <div style="float: right; width: 45%;">
                <p>Pour l'église</p>
                <br><br><br>
                _______________________
            </div>
            <div style="clear: both;"></div>
        </div>

        <div class="footer">
            <p>Reçu généré le <?= date('d/m/Y à H:i') ?></p>
            <p>Merci pour votre générosité ! Que Dieu vous bénisse.</p>
        </div>

        <div class="no-print" style="margin-top: 30px; text-align: center;">
            <button onclick="window.print()" style="padding: 10px 20px;">
                <i class="fas fa-print"></i> Imprimer
            </button>
        </div>
    </div>
</body>
</html>
