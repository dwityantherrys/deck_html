<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
 <title>@yield('page:title')</title>

 <!-- Scripts -->
<!-- Styles -->

 <style>
     .title, .subtitle { text-align: center; }
     .center-image {
        display: block;
        margin-left: auto;
        margin-right: auto;
        }
     /* .title{ position: relative; margin-bottom: 5px; } */
     /* .title:after{ 
        position: absolute;
        top: 40px; */
        /* left: 0px; */
        /* content: ''; 
        width: 33%; 
        height: 1px; 
        padding: 0.1px; 
        background: black; 
    } */
     .subtitle{ font-weight: normal; font-size: 18px; }
     
     table{ width: 100%; }
     table .field-separator{ width: 5px; }
     table.table-information,
     table.table-signment{ margin-top: 20px; }
     table.table-items,
     table.table-summaries,
     table.table-full-border,
     table.table-additional{ 
         border-collapse: collapse; 
         border: 1px solid black; 
    }
     table.table-items thead,
     table.table-summaries tr td,
     table.table-full-border tr td,
     table.table-additional tr td{ 
         border-bottom: 1px solid black; 
     }
     table.table-items thead tr th{ text-align: center; }
     table.table-items thead tr th,
     table.table-items tbody tr td,
     table.table-full-border tbody tr td,
     table.table-additional tbody tr td{ border-right: 1px solid black; }
     table.table-items thead tr th:last-child,
     table.table-items tbody tr td:last-child{ border-right: 0px; }
     table.table-items tfoot tr td{ 
         border-top: 1px solid black;
         border-right: 1px solid black; 
    }
    table.table-items td,
    table.table-summaries td,
    table.table-full-border td{ padding: 0px 10px 0px 10px; }

    .label{ font-weight: bold; }
    .text-align-center { text-align: center; }
    .text-align-right { text-align: right; }

    .watermark{
         position: fixed;
         top: 0px;
         left: 0px;
         height: 100%;
         width: 100%;
         z-index: -1;
         display: flex;
         justify-content: center;
         align-items: center;
     }
    .br {
        display: block;
        margin-bottom: 0em;
    }
        
    .brsmall {
        display: block;
        margin-bottom: -.2em;
    }
        
    .brxsmall {
        display: block;
        margin-bottom: -.4em;
    }
 </style>
</head>

<body>
    <table border="0">
        <tr>
            <td>
                <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('/img/logo_baru.jpeg'))) }}" width="230px">
            </td>
            <td class="text-align-right" valign="text-bottom">
                <p class="text-align-right">
                    PT Catur Sentosa Adiprana
                    <span class="brxsmall"></span>
                    Jl. Daan Mogot Raya No.234, Jakarta Barat 11510
                    <span class="brxsmall"></span>
                    Hotline (24hrs) +628123456789
                    <span class="brxsmall"></span>
                    Email: csa@gmail.com
                    <span class="brxsmall"></span>
                    Website: www.csa.com
                </p>    
            </td>
        </tr>
    </table>

    @yield('page:content')
</body>
</html>