<?php
require_once 'CardToggle.php';

// ══════════════════════════════════════════════════════
//  模擬資料庫資料
// ══════════════════════════════════════════════════════

$drugs = [
    [
        'name'            => '阿斯匹靈',
        'generic'         => 'Acetylsalicylic Acid',
        'category'        => '非類固醇消炎止痛藥',
        'indication'      => '解熱、鎮痛、抗發炎、預防血栓',
        'dosage'          => '325–650 mg，每 4–6 小時，飯後服用',
        'max_single'      => '650',
        'contraindication'=> "胃潰瘍患者\n孕婦（第三孕期）\n12 歲以下兒童",
        'interactions'    => 'Warfarin、Ibuprofen、酒精',
        'warning'         => "服用期間請勿飲酒\n出現耳鳴請立即停藥並回診",
        'color'           => 'warning',
    ],
    [
        'name'            => '阿莫西林',
        'generic'         => 'Amoxicillin',
        'category'        => '青黴素類抗生素',
        'indication'      => '細菌感染：中耳炎、肺炎、鼻竇炎、泌尿道感染',
        'dosage'          => '250–500 mg，每 8 小時，療程 7–14 天',
        'max_single'      => '500',
        'contraindication'=> "對盤尼西林過敏者\n傳染性單核球增多症患者",
        'interactions'    => '口服避孕藥、Methotrexate',
        'warning'         => "完成整個療程，不可中途停藥\n出現皮疹請立即就醫",
        'color'           => 'info',
    ],
    [
        'name'            => '二甲雙胍',
        'generic'         => 'Metformin',
        'category'        => '雙胍類降血糖藥',
        'indication'      => '第二型糖尿病血糖控制',
        'dosage'          => '500 mg 起始，每日 2–3 次，隨餐服用',
        'max_single'      => '1000',
        'contraindication'=> "腎功能不全（eGFR < 30）\n嚴重肝臟疾病\n酒精中毒",
        'interactions'    => '碘造影劑、酒精、部分利尿劑',
        'warning'         => "定期監測腎功能\n造影檢查前 48 小時停藥",
        'color'           => 'safe',
    ],
];

$employees = [
    ['name'=>'王大明','dept'=>'研發部','title'=>'資深工程師','hire'=>'2019-03-15','allergy'=>'Penicillin','grade'=>'A'],
    ['name'=>'林美華','dept'=>'行銷部','title'=>'行銷專員',  'hire'=>'2021-07-01','allergy'=>'無',        'grade'=>'B+'],
    ['name'=>'陳建國','dept'=>'財務部','title'=>'財務主任',  'hire'=>'2016-11-20','allergy'=>'磺胺類',    'grade'=>'A+'],
];

$sop_steps = [
    ['step'=>1,'title'=>'確認病患身分','desc'=>"核對姓名與病歷號\n確認過敏史\n確認目前用藥清單",'color'=>'sky'],
    ['step'=>2,'title'=>'評估生命徵象','desc'=>"量測血壓、心跳、體溫、血氧\n記錄於護理紀錄\n異常值需立即通報",'color'=>'info'],
    ['step'=>3,'title'=>'執行醫囑','desc'=>"再次核對藥物劑量\n確認給藥途徑\n記錄給藥時間",'color'=>'lavender'],
    ['step'=>4,'title'=>'觀察反應','desc'=>"給藥後 15 分鐘內觀察\n記錄任何不良反應\n若有異常立即呼叫醫師",'color'=>'safe'],
];
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
<meta charset="UTF-8">
<title>CardToggle PHP Class 範例</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  :root {
    --bg:      #0c0d0c;
    --bg-alt:  #240518;
    --shell:   #c6c7bd;
    --lavender:#C3A5E5;
    --special: #C8DD5A;
    --warning: #F08080;
    --sky:     #08a9d1;
    --safe:    #40c99a;
    --yellow:  #DECA4B;
    --info:    #5fafed;
    --stone:   #7090A8;
    --pink:    #FFB3D9;
    --orange:  #eda109;
    --border:  #222322;
  }
  html, body {
    min-height: 100vh;
    background: var(--bg);
    color: var(--shell);
    font-family: 'Segoe UI','PingFang TC','Microsoft JhengHei',sans-serif;
    font-size: 18px;
    line-height: 1.8;
  }
  .wrap { max-width: 1100px; margin: 0 auto; padding: 48px 40px 100px; }

  h1 { font-size: 1.9rem; color: var(--lavender); margin-bottom: 6px; }
  .subtitle { color: var(--stone); font-size: 1rem; margin-bottom: 48px; }

  .section { margin-bottom: 56px; }
  .section-title {
    font-size: 1.05rem; font-weight: 700;
    color: var(--special);
    border-left: 3px solid var(--special);
    padding-left: 12px;
    margin-bottom: 20px;
    letter-spacing: 0.05em;
  }

  .desc-card {
    background: #111211;
    border: 1px solid #252625;
    border-radius: 8px;
    padding: 16px 20px;
    margin-bottom: 18px;
    font-size: 0.92rem;
    color: #9a9b96;
    line-height: 1.9;
  }
  .desc-card code {
    background: #1e201e; color: var(--sky);
    padding: 1px 6px; border-radius: 4px;
    font-family: 'Cascadia Code','Fira Code','Consolas',monospace;
    font-size: 0.85rem;
  }

  .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
  .grid-3 { display: grid; grid-template-columns: repeat(3,1fr); gap: 14px; }

  hr { border: none; border-top: 1px solid var(--border); margin: 44px 0; }

  /* hint 顯示區 */
  .hint-area { min-height: 20px; margin-top: 10px; }

  /* 員工表格 */
  .data-table { width: 100%; border-collapse: collapse; font-size: 0.95rem; }
  .data-table th { padding: 10px 14px; color: var(--lavender); font-weight: 700; border-bottom: 2px solid var(--border); text-align: left; background: #141514; }
  .data-table td { padding: 10px 14px; border-bottom: 1px solid var(--border); }
  .data-table tbody tr:hover { background: #141514; }

  /* badge */
  .badge {
    display: inline-block; padding: 1px 8px;
    border-radius: 4px; font-size: 0.76rem; font-weight: 700;
  }
  .badge-php { background: #2a1055; color: var(--lavender); }
</style>
</head>
<body>
<div class="wrap">

  <h1>CardToggle PHP Class 範例</h1>
  <p class="subtitle">模擬從資料庫讀取資料，展示四種方法的實際用法</p>

  <!-- ══════════════════════════════════════
       範例一：reveal() — 大塊 HTML 揭露
  ══════════════════════════════════════ -->
  <div class="section">
    <div class="section-title">範例一：<code>CardToggle::reveal()</code> — 藥物資訊揭露（slide 動畫）</div>
    <div class="desc-card">
      點擊卡片後，內容替換為從資料庫組成的 HTML 表格。<br>
      <code>escape=false</code>（預設），由 PHP 自行組 HTML；含換行的欄位手動呼叫 <code>nl2br(htmlspecialchars(...))</code>。
    </div>

    <div class="grid-3">
    <?php foreach ($drugs as $i => $drug):
      // 組成揭露後顯示的 HTML
      $c = CardToggle::colorHex($drug['color']);
      $revealHtml = '
        <p style="margin:0 0 10px 0;font-weight:700;color:' . $c . '">'
          . htmlspecialchars($drug['name'], ENT_QUOTES, 'UTF-8')
          . ' <small style="font-weight:400;opacity:0.7">'
          . htmlspecialchars($drug['generic'], ENT_QUOTES, 'UTF-8')
          . '</small></p>
        <table style="width:100%;border-collapse:collapse;font-size:0.88rem">
          <tr style="border-bottom:1px solid rgba(255,255,255,0.08)">
            <td style="padding:5px 8px;color:' . $c . ';width:80px">分類</td>
            <td style="padding:5px 8px">' . htmlspecialchars($drug['category'], ENT_QUOTES, 'UTF-8') . '</td>
          </tr>
          <tr style="border-bottom:1px solid rgba(255,255,255,0.08)">
            <td style="padding:5px 8px;color:' . $c . '">適應症</td>
            <td style="padding:5px 8px">' . htmlspecialchars($drug['indication'], ENT_QUOTES, 'UTF-8') . '</td>
          </tr>
          <tr style="border-bottom:1px solid rgba(255,255,255,0.08)">
            <td style="padding:5px 8px;color:' . $c . '">劑量</td>
            <td style="padding:5px 8px">' . htmlspecialchars($drug['dosage'], ENT_QUOTES, 'UTF-8') . '</td>
          </tr>
          <tr style="border-bottom:1px solid rgba(255,255,255,0.08)">
            <td style="padding:5px 8px;color:' . $c . '">禁忌</td>
            <td style="padding:5px 8px">' . nl2br(htmlspecialchars($drug['contraindication'], ENT_QUOTES, 'UTF-8')) . '</td>
          </tr>
          <tr>
            <td style="padding:5px 8px;color:' . $c . '">注意</td>
            <td style="padding:5px 8px">' . nl2br(htmlspecialchars($drug['warning'], ENT_QUOTES, 'UTF-8')) . '</td>
          </tr>
        </table>';

      echo CardToggle::reveal(
          '📋 ' . htmlspecialchars($drug['name'], ENT_QUOTES, 'UTF-8') . '　<small style="opacity:0.5;font-size:0.82rem">點擊查看詳情</small>',
          $revealHtml,
          [
              'color'      => $drug['color'],
              'color_after'=> $drug['color'],
              'animation'  => 'slide',
              'number'     => $i + 1,
          ]
      );
    endforeach ?>
    </div>

    <div class="desc-card" style="margin-top:16px">
      <span class="badge badge-php">PHP</span>
      <code>CardToggle::reveal($label, $htmlContent, ['color' => 'warning', 'animation' => 'slide'])</code>
    </div>
  </div>

  <!-- ══════════════════════════════════════
       範例二：revealArray() — DB row 直接展開
  ══════════════════════════════════════ -->
  <div class="section">
    <div class="section-title">範例二：<code>CardToggle::revealArray()</code> — 員工資料直接展開</div>
    <div class="desc-card">
      直接把資料庫 row 的關聯陣列傳入，class 自動產生定義清單，所有欄位自動跳脫，<code>\n</code> 自動轉 <code>&lt;br&gt;</code>。<br>
      不需要手動組 HTML，最適合快速展示一筆 DB 資料。
    </div>

    <table class="data-table">
      <thead>
        <tr><th>姓名</th><th>部門</th><th>職稱</th><th>考核</th></tr>
      </thead>
      <tbody>
      <?php foreach ($employees as $emp):
        $gradeColor = str_starts_with($emp['grade'], 'A') ? 'var(--safe)' : 'var(--sky)';
      ?>
        <tr>
          <td>
            <?= CardToggle::revealArray(
                '👤 ' . htmlspecialchars($emp['name'], ENT_QUOTES, 'UTF-8') . '　<small style="opacity:0.5;font-size:0.82rem">點擊展開</small>',
                [
                    '部門'     => $emp['dept'],
                    '職稱'     => $emp['title'],
                    '到職日'   => $emp['hire'],
                    '藥物過敏' => $emp['allergy'],
                    '考核評等' => $emp['grade'],
                ],
                ['color' => 'lavender', 'animation' => 'fade']
            ) ?>
          </td>
          <td><?= htmlspecialchars($emp['dept'],  ENT_QUOTES, 'UTF-8') ?></td>
          <td><?= htmlspecialchars($emp['title'], ENT_QUOTES, 'UTF-8') ?></td>
          <td style="color:<?= $gradeColor ?>;font-weight:700"><?= htmlspecialchars($emp['grade'], ENT_QUOTES, 'UTF-8') ?></td>
        </tr>
      <?php endforeach ?>
      </tbody>
    </table>

    <div class="desc-card" style="margin-top:16px">
      <span class="badge badge-php">PHP</span>
      <code>CardToggle::revealArray($label, $dbRow, ['color' => 'lavender'])</code>　
      所有欄位自動 <code>htmlspecialchars + nl2br</code>，無需手動處理。
    </div>
  </div>

  <!-- ══════════════════════════════════════
       範例三：quiz() — 藥學測驗
  ══════════════════════════════════════ -->
  <div class="section">
    <div class="section-title">範例三：<code>CardToggle::quiz()</code> — 藥學知識測驗</div>
    <div class="desc-card">
      從藥物資料庫動態產生測驗題，答案來自 DB 欄位。答對後顯示完整解說 HTML。<br>
      支援最大嘗試次數、提示、自動跳題（<code>auto_next</code>）。
    </div>

    <?php
    // hint 顯示區（共用）
    echo '<div id="quiz-hint-area" class="hint-area"></div>';

    echo CardToggle::groupOpen(['mode' => 'slide', 'theme' => 'warning', 'show_pages' => true]);

    foreach ($drugs as $drug):
      // 答對後顯示的解說
      $c = CardToggle::colorHex($drug['color']);
      $successHtml = '
        <p style="margin:0 0 8px 0;color:' . $c . ';font-weight:700">
          <i class="bi bi-check-circle-fill"></i> 正確！最大單次劑量為 '
          . htmlspecialchars($drug['max_single'], ENT_QUOTES, 'UTF-8') . ' mg
        </p>
        <p style="margin:0;font-size:0.88rem;opacity:0.8">'
          . htmlspecialchars($drug['dosage'], ENT_QUOTES, 'UTF-8') . '</p>';

      echo CardToggle::quiz(
          htmlspecialchars($drug['name'], ENT_QUOTES, 'UTF-8')
              . ' 的最大單次劑量是多少 mg？',
          $drug['max_single'],
          $successHtml,
          [
              'color'           => $drug['color'],
              'max_attempts'    => 3,
              'placeholder'     => '輸入數字（mg）',
              'hint'            => '提示：' . htmlspecialchars($drug['dosage'], ENT_QUOTES, 'UTF-8'),
              'hint_target'     => 'quiz-hint-area',
              'error_message'   => '答案不正確，請再想想',
              'success_color'   => 'safe',
              'auto_next'       => true,
              'submit_text'     => '確認答案',
              'hint_text'       => '需要提示',
              'reset_text'      => '重新作答',
              'size'            => 'lg',
          ]
      );
    endforeach;

    echo CardToggle::groupClose();
    ?>

    <div class="desc-card" style="margin-top:16px">
      <span class="badge badge-php">PHP</span>
      <code>CardToggle::quiz($question, $answer, $successHtml, ['max_attempts'=>3, 'auto_next'=>true])</code><br>
      答對後自動跳下一題，答題記錄 DB 欄位 <code>max_single</code> 作為正確答案。
    </div>
  </div>

  <!-- ══════════════════════════════════════
       範例四：card() + groupOpen() — SOP 步驟卡
  ══════════════════════════════════════ -->
  <div class="section">
    <div class="section-title">範例四：<code>CardToggle::card()</code> + 群組 — SOP 步驟卡（stack 模式）</div>
    <div class="desc-card">
      靜態展示用途，從資料庫撈出 SOP 步驟後，每筆資料渲染成一張帶序號的卡片，以 stack 模式垂直排列。<br>
      使用 <code>revealText()</code> 讓卡片本身點擊後展開步驟說明（含換行）。
    </div>

    <?php
    echo CardToggle::groupOpen(['mode' => 'stack']);
    foreach ($sop_steps as $step):
      echo CardToggle::revealText(
          '<strong style="color:' . CardToggle::colorHex($step['color']) . '">'
              . 'Step ' . $step['step'] . '　' . htmlspecialchars($step['title'], ENT_QUOTES, 'UTF-8')
              . '</strong>　<small style="opacity:0.5;font-size:0.82rem">點擊展開說明</small>',
          $step['desc'],
          [
              'color'      => $step['color'],
              'color_after'=> $step['color'],
              'number'     => $step['step'],
              'animation'  => 'slide',
          ]
      );
    endforeach;
    echo CardToggle::groupClose();
    ?>

    <div class="desc-card" style="margin-top:16px">
      <span class="badge badge-php">PHP</span>
      <code>CardToggle::revealText($label, $plainText, ['color'=>'sky', 'number'=>1])</code>　
      純文字欄位（含 <code>\n</code>）自動跳脫與換行，無需手動處理。
    </div>
  </div>

  <!-- ══════════════════════════════════════
       範例五：混合使用 + 顏色展示
  ══════════════════════════════════════ -->
  <div class="section">
    <div class="section-title">範例五：所有顏色 × dashed 邊框展示</div>
    <div class="desc-card">
      13 種顏色 × dashed 邊框，可搭配任意方法使用。
    </div>

    <div class="grid-3" style="margin-bottom:14px">
    <?php
    $colors = ['safe','warning','info','special','sky','lavender','attention','salmon','pink','orange','stone','brown','shell'];
    foreach ($colors as $col):
      echo CardToggle::card(
          '<span style="color:' . CardToggle::colorHex($col) . ';font-weight:700">' . $col . '</span>',
          ['color' => $col, 'dashed' => true, 'size' => 'sm']
      );
    endforeach;
    ?>
    </div>
  </div>

  <hr>

  <!-- ══════════════════════════════════════
       方法速查
  ══════════════════════════════════════ -->
  <div class="section">
    <div class="section-title">方法速查</div>
    <div class="desc-card">
      <table style="width:100%;border-collapse:collapse;font-size:0.88rem">
        <thead>
          <tr style="border-bottom:1px solid #2a2b2a">
            <th style="padding:8px 12px;text-align:left;color:var(--lavender)">方法</th>
            <th style="padding:8px 12px;text-align:left;color:var(--lavender)">escape 預設</th>
            <th style="padding:8px 12px;text-align:left;color:var(--lavender)">\n→&lt;br&gt;</th>
            <th style="padding:8px 12px;text-align:left;color:var(--lavender)">適用情境</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $methods = [
              ['reveal()',       'false', '手動', '自行組 HTML，最彈性'],
              ['revealText()',   'true',  '✅ 自動', '純文字 DB 欄位，最簡單'],
              ['revealArray()',  'true',  '✅ 自動', '直接丟 DB row 關聯陣列'],
              ['quiz()',         'false', '手動', '測驗題，答案來自 DB'],
              ['card()',         'false', '手動', '靜態卡片，群組內使用'],
              ['groupOpen()',    '—',     '—',    '群組容器開始'],
              ['groupClose()',   '—',     '—',    '群組容器結束'],
              ['script()',       '—',     '—',    '引入 JS，只輸出一次'],
          ];
          foreach ($methods as $m):
          ?>
          <tr style="border-bottom:1px solid #1e201e">
            <td style="padding:7px 12px;color:var(--sky);font-family:monospace"><?= $m[0] ?></td>
            <td style="padding:7px 12px"><?= $m[1] ?></td>
            <td style="padding:7px 12px"><?= $m[2] ?></td>
            <td style="padding:7px 12px;color:#8a8b86"><?= $m[3] ?></td>
          </tr>
          <?php endforeach ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<?= CardToggle::script('card-toggle.js') ?>
</body>
</html>
