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
include('../../RedirectModulesInc.php');
echo "<form name='add' id='add' action='" . PreparePHP_SELF() . "' method='POST'>";
DrawBC("" . _students . " > " . ProgramTitle());

if ($_REQUEST['day__start'] && $_REQUEST['month__start'] && $_REQUEST['year__start']) {
    while (!VerifyDate($start_date = $_REQUEST['day__start'] . '-' . $_REQUEST['month__start'] . '-' . $_REQUEST['year__start']))
        $_REQUEST['day__start']--;
} else
    $start_date = date('Y-m') . '-01';

if ($_REQUEST['day__end'] && $_REQUEST['month__end'] && $_REQUEST['year__end']) {
    while (!VerifyDate($end_date = $_REQUEST['day__end'] . '-' . $_REQUEST['month__end'] . '-' . $_REQUEST['year__end']))
        $_REQUEST['day__end']--;
} else
    $end_date = DBDate('mysql');
$start_date = date('Y-m-d', strtotime($start_date));
$end_date = date('Y-m-d', strtotime($end_date));
$selectedType = 'Pending';
if ($_REQUEST['to_status']) {
    $selectedType = $_REQUEST['to_status'];
}
echo '<div class="panel panel-default">';

echo '<div class="panel-body">
        <div class="form-inline">
        <div class="row">
        <div class="col-md-12">' . PrepareDateSchedule($start_date, '_start') . ' &nbsp; <label class="control-label"> &nbsp; - &nbsp; </label> &nbsp; ' . PrepareDateSchedule($end_date, '_end') . ' &nbsp; 
        <select class="form-control"  name="to_status" id="feeTypeSelect"  >' .
    '<option value="Pending">Unpaid</option>' .
    '<option value="Paid" >Paid</option>' .

    '</select>
        <input type="submit" class="btn btn-primary" value="' . _go . '">
     
        </div>
        </div>
        </div>
        </div>';


echo '</div>';

echo '</form>';

$fees_RET = DBGet(DBQuery(
    "SELECT CONCAT(st.LAST_NAME, ', ', st.FIRST_NAME) AS FULL_NAME, sf.fee_type AS FEE_TYPE, sf.amount AS AMOUNT, sf.status AS STATUS, sf.date_of_payment AS DATE_OF_PAYMENT FROM students st " .
        "JOIN student_fees sf ON sf.student_id = st.student_id " .
        "WHERE DATE_OF_PAYMENT >= '{$start_date}' AND DATE_OF_PAYMENT <= '{$end_date}' AND STATUS='{$selectedType}'"
));

$columns = array(
    'FULL_NAME' => _student,
    'FEE_TYPE' => 'Fee Type',
    'AMOUNT' => 'AMOUNT',
    'STATUS' => 'STATUS',
    'DATE_OF_PAYMENT' => 'DATE_OF_PAYMENT',
);

echo '<div class="panel panel-default">';
ListOutput($fees_RET, $columns, _feesRecord, _feeRecords);
echo '</div>';

echo "<FORM action=ForExport.php?modname=$_REQUEST[modname]&modfunc=save&include_inactive=&_search_all_schools=&_openSIS_PDF=true method=POST target=_blank>";
  
 

echo '<div class="text-right p-b-20 p-r-20"><INPUT type=submit class="btn btn-primary" value=\'Print PDF\' ></div>';
echo "</FORM>";
?>
<script type="text/javascript">
    function printExcel() {
        let data = [];
        let headerRow = ["Student", "", "Fee Type", "Amount", "Status", "Date Of Payment"];
        data.push(headerRow.join(','));
        $('#results').find('tr').each(function() {
            let row = [];
            $(this).find('td').each(function() {
                row.push($(this).text());
            });
            data.push(row.join(','));
        });
        var csvContent = 'data:text/csv;charset=utf-8,';
        csvContent += data.join('\n');

        // Create a temporary link element
        var link = document.createElement('a');
        link.setAttribute('href', encodeURI(csvContent));
        link.setAttribute('download', 'data.csv');

        // Trigger the download
        link.click();
    }
</script>