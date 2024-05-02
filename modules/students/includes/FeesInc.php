<?php

#**************************************************************************
#  openSIS is a free student information system for public and non-public 
#  schools from Open Solutions for Education, Inc. web: www.os4ed.com
#
#  openSIS is  web-based, open source, and comes packed with features that 
#  include student demographic info, scheduling, grade book, attendance, 
#  report cards, eligibility, transcripts, parent portal, 
#  student portal and more.   
#
#  Visit the openSIS web site at http://www.opensis.com to learn more.
#  If you have question regarding this system or the license, please send 
#  an email to info@os4ed.com.
#
#  This program is released under the terms of the GNU General Public License as  
#  published by the Free Software Foundation, version 2 of the License. 
#  See license.txt.
#
#  This program is distributed in the hope that it will be useful,
#  but WITHOUT ANY WARRANTY; without even the implied warranty of
#  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#  GNU General Public License for more details.
#
#  You should have received a copy of the GNU General Public License
#  along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
#***************************************************************************************


echo '
<head>
        <style>

        input[type="text"],input[type="number"],input[type="password"]{
            padding: 5px;
            border-radius: 5px;
            border: 1px solid #b4b4b4;
            margin-top:10px;
        }
        #toastContainer {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 9999;
        }

        .toast {
        background-color: #333;
        color: #fff;
        padding: 10px 20px;
        border-radius: 5px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        display: none;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
        }
        
        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 50%;
            text-align: center;
            position: relative;
        }
        
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            position: absolute;
            top: 5px;
            right: 10px;
            cursor: pointer;
        }
        
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
        
        </style>
</head>';



function handleFormSubmission()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $feeType = strval($_POST['feeType']);
        $amount = $_POST['amount'];
        $date = $_POST['date'];
        $comment = $_POST['comment'] ?? "";
        $studentId = UserStudentID();
        $userId = UserID();

        $sql = "INSERT INTO student_fees (amount, fee_type, student_id, status, payment_type, comment, date_of_payment, updated_by)
                VALUES($amount, '$feeType', $studentId , 'Pending', 'flocash', '$comment', '$date', $userId)";

        DBQuery($sql);

        header("Location: " . $_SERVER['REQUEST_URI']);
        exit();
    }
}

// generate fee table row
function generateFeeTableRow()
{
    return '<tr>
                <td><select class="form-control" required name="feeType" id="feeTypeSelect">
                        <option value="monthly">Monthly Fee</option>
                        <option value="semester">Semester Fee</option>
                        <option value="tuition">Tuition Fee</option>
                        <option value="lab">Lab Fee</option>
                    </select>
                </td>
                <td><input class="form-control required amount-input" type="number" name="amount" required></td>
                <td><input class="form-control" required type="date" name="date" required></td>
                <td><select class="form-control" name="paymentType" id="paymentTypeSelect" disabled>
                        <option value="flocash" selected>Flocash</option>
                    </select>
                </td>
                <td><input class="form-control" disabled type="text" value="Pending" name="status" required></td>
                <td><input type="text" class="form-control" value="" name="comment"></td>
            </tr>';
}

//  generate fee management table (Admin side table rendering)
function generateFeeManagementTable()
{
    $tableHeader = '<form method="post" action="">
                        <div class="container">
                            <h2>Fee Management</h2>
                            <br><br>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Fee Type</th>
                                        <th>Amount</th>
                                        <th>Date</th>
                                        <th>Payment Type</th>
                                        <th>Status</th>
                                        <th>Comment</th>
                                    </tr>
                                </thead>
                                <tbody id="student-fee-table-body">';

    $row = generateFeeTableRow();

    $tableFooter = '</tbody>
                    </table>
                    <div class="text-right" style="margin-top:10px;">
                        <button type="submit" class="btn btn-primary">Add Fee</button>
                    </div>
                </div>
            </form>';

    return $tableHeader . $row . $tableFooter;
}

// Function to generate payment table (Parent side table rendering)
function generatePaymentTable()
{
    $studentId = UserStudentID();
    $feeQuery = "SELECT * FROM student_fees WHERE student_id=$studentId AND status='Pending'";
    $studentFees = DBQuery($feeQuery);
    $totalPayment = 0;
    $paymentRow = '';

    if ($studentFees) {
        foreach ($studentFees as $option) {
            $totalPayment += $option["amount"];
            $paymentRow .= '<tr>
                                <td>' . $option["fee_type"] . '</td>
                                <td>' . $option["amount"] . '</td>
                                <td>' . $option["date_of_payment"] . '</td>
                                <td>' . $option["payment_type"] . '</td>
                                <td>' . $option["status"] . '</td>
                                <td>' . $option["comment"] . '</td>
                            </tr>';
        }

        $paymentTable = '
                            <div class="container">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Fee Type</th>
                                            <th>Amount</th>
                                            <th>Date</th>
                                            <th>Payment Type</th>
                                            <th>Status</th>
                                            <th>Comment</th>
                                        </tr>
                                    </thead>
                                    <tbody id="student-fee-table-body">' . $paymentRow . '</tbody>
                                </table>
                                <div class="text-right" style="margin-top:10px;">
                                    <input type="hidden" id="studentId" value="' . $studentId . '"/>';

        //Conditionally display pay fees button based on the number of student fee rows 
        if ($studentFees && mysqli_num_rows($studentFees) > 0) {
            $paymentTable .= '<button type="button" id="showModalBtn"   class="btn btn-success">Pay Fees</button>';
        }


        $paymentTable .= '</div>
                            </div>
                        
                        <div class="container">
                            <div class="row">
                                <div class="col-md-6 col-md-offset-3 text-center">
                                    <h5>Total Fee <span id="total_payment">' . $totalPayment . ' USD</span></h5> 
                                </div>
                            </div>
                        </div>
                        <div id="toastContainer">
                            <div id="toastMessage" class="toast"></div>
                        </div>';

        return $paymentTable;
    }

    return '';
}

//  generate modal
function generateModal()
{
    return ' 
            <div id="paymentModal" class="modal">
                <div class="modal-content">
                    <span id="closeModal" class="close">&times;</span>
                    <h2>Payment Details</h2>
                    <form>
                    <div class="row">
                        <div class="col-md-6">
                            <input class="form-control" placeholder="Card Holder" required type="text" name="cardHolder" required>
                        </div>
                        <div class="col-md-6">
                            <input class="form-control" placeholder="Card Number" required type="text" name="cardNumber" required>  
                        </div>
                        <div class="col-md-6 mt-2">
                            <input class="form-control" placeholder="Card Pin" required type="password" name="cardPin" required>  
                        </div>
                        <div class="col-md-6 mt-2">
                            <input class="form-control" placeholder="Card Expiry Month" required type="number" name="expireMonth" required>  
                         </div>
                        <div class="col-md-6 mt-2">
                            <input class="form-control" placeholder="Card Expiry Year" required type="number" name="expireYear" required>  
                        </div>
                        <div class="col-md-6 mt-2">
                            <input class="form-control" placeholder="Card CVV" required type="number" name="cvv" required>  
                        </div>
                    </div>
                    <p>Total Price: $50</p>
                    <p>Payment Method:</p>
                    <button type="submit" id="payButton" onclick="payAll()" class="btn btn-success">Confirm Payment</button>
                    </form>
                </div>
            </div>';
}



handleFormSubmission();

if (UserID()) {
    $userId = UserID();
    //If user is parent
    if (UserProfileID() == 4) {
        echo generatePaymentTable();
        echo generateModal();
    } else {
        echo generateFeeManagementTable();
    }
}



?>

<script type="text/javascript">
    let studentFees = [];


    async function payAll() {
 
        let totalPayment = 0;
        const credentials = {
            merchantEmail: "your_email@example.com",
            apiUsername: "your_username",
            apiPassword: "your_password",
        }
        console.log($('#student-fee-table-body tr'));
        $('#student-fee-table-body tr').each(function() {
            var amount = parseFloat($(this).find('td:eq(1)').text());
            totalPayment += amount ? amount : 0;
        });
        const cardInfo = {
            cardHolder: $("input.form-control[name='cardHolder']").val(),
            cardNumber: $("input.form-control[name='cardNumber']").val(),
            cardPin: $("input.form-control[name='cardPin']").val(),
            expireMonth: $("input.form-control[name='expireMonth']").val(),
            expireYear: $("input.form-control[name='expireYear']").val(),
            cvv: $("input.form-control[name='cvv']").val()
        }
        let requestBody = {
            order: {
                amount: totalPayment,
                orderId: "AT000001",
                item_name: "School Fee",
                item_price: "1000",
                quantity: "1",
                currency: "USD"
            },
            merchant: {
                merchantAccount: credentials.merchantEmail
            },
            payer: {
                country: "ET",
                firstName: $("input.form-control[name='cardHolder']").val(),
                lastName: $("input.form-control[name='cardHolder']").val(),
                mobile: "+021123213232",
                email: "demo@email.com"
            },
            cardInfo: cardInfo
        };

        const jsonData = JSON.stringify(requestBody);

        const apiUrl = "https://sandbox.flocash.com/rest/v2/orders/";


        $('#payButton').prop('disabled', true);

        //Here initiate the payment process
        $.ajax({
            type: "POST",
            url: apiUrl,
            data: jsonData,
            contentType: "application/json",
            beforeSend: function(xhr) {
                const authHeader = "Basic " + btoa(credentials.apiUsername + ":" + credentials.apiPassword);
                xhr.setRequestHeader("Authorization", authHeader);
            },
            success: function(response) {

                //If the first call was successful, get the values returned from the first one and add a put request
                const respData = {
                    order: {
                        traceNumber: response?.order?.traceNumber || null
                    },
                    payOption: {
                        id: response?.paymentOptions?.cards?.[0]?.id || null
                    },
                    cardInfo: cardInfo
                };

                $.ajax({
                    type: "PUT",
                    url: apiUrl,
                    data: JSON.stringify(respData),
                    contentType: "application/json",
                    beforeSend: function(xhr) {
                        const authHeader = "Basic " + btoa(username + ":" + password);
                        xhr.setRequestHeader("Authorization", authHeader);
                    },
                    success: function(response2) {



                        //If we get the response that holds our final payment process either redirect using the url provided from response
                        //or post a data with required param values
                        const studentId = $('#studentId').val();
                        const payResp = {
                            MD: response2?.order?.redirect?.params?.MD,
                            PaReq: response2?.order?.redirect?.params?.PaReq,
                            TermUrl: response2?.order?.redirect?.params?.TermUrl,
                            URL: response2?.order?.redirect?.url,
                            StudentId: studentId
                        };


                        if (response2?.order?.status === '0009') {
                            window.open(response2?.order?.redirect?.url, '_blank');
                            return;
                        }
                        $.ajax({
                            type: "POST",
                            url: "modules/students/includes/Pay.php",
                            data: payResp,
                            success: function(response) {
                                $('#toastMessage').text(' Payment is Successful');
                                $('.toast').fadeIn().delay(3000).fadeOut();
                                $('#payButton').prop('disabled', false);

                                $("#paymentModal").hide();
                                $("#paymentModal").modal('hide');

                            },
                            error: function(xhr, status, error) {
                                $('#toastMessage').text(' Payment has Failed');
                                $('.toast').fadeIn().delay(3000).fadeOut();
                                $('#payButton').prop('disabled', false);
                            }
                        });
                    },
                    error: function(xhr, status, error) {
                        $('#toastMessage').text(' Payment has Failed');
                        $('.toast').fadeIn().delay(3000).fadeOut();
                        $('#payButton').prop('disabled', false);
                    }
                });

            },
            error: function(xhr, status, error) {
                $('#toastMessage').text(' Payment has Failed');
                $('.toast').fadeIn().delay(3000).fadeOut();
                $('#payButton').prop('disabled', false);
            }
        });



    }

    $(document).ready(function() {
        $("#showModalBtn").click(function() {
            $("#paymentModal").modal('show');
        });
        $("#closeModal").click(function() {
            $("#paymentModal").modal('hide');
        });
    });
</script>