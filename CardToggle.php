<?php
/**
 * CardToggle.php
 * PHP 渲染類別，搭配 card-toggle.js 使用
 * 負責從 PHP/DB 資料產生正確的 card-toggle HTML 標記
 *
 * 使用方式：
 *   require_once 'CardToggle.php';
 *
 *   // 揭露模式
 *   echo CardToggle::reveal('點擊查看診斷結果', $htmlContent, ['color' => 'info']);
 *
 *   // 測驗模式
 *   echo CardToggle::quiz('阿斯匹靈最大單次劑量？', '650', $successHtml, ['color' => 'warning']);
 *
 *   // 群組
 *   echo CardToggle::groupOpen(['mode' => 'slide', 'theme' => 'sky']);
 *   echo CardToggle::card('第一張卡片內容', ['color' => 'info']);
 *   echo CardToggle::card('第二張卡片內容', ['color' => 'safe']);
 *   echo CardToggle::groupClose();
 */
class CardToggle
{
    /** 已輸出 script 旗標，避免重複引入 */
    private static bool $scriptRendered = false;

    /** 已使用 id 清單 */
    private static array $usedIds = [];

    /** 有效的顏色名稱 */
    private const VALID_COLORS = [
        'safe', 'warning', 'info', 'special', 'sky', 'lavender',
        'attention', 'salmon', 'brown', 'shell', 'pink', 'orange', 'stone',
    ];

    /** 有效的尺寸 */
    private const VALID_SIZES = ['xsm', 'sm', 'lg', 'xlg'];

    // ──────────────────────────────────────────────
    //  公開方法
    // ──────────────────────────────────────────────

    /**
     * 揭露模式：點擊後將卡片內容替換為指定 HTML
     * escape 預設 false（由呼叫端自行組 HTML）
     *
     * @param string $label   卡片上顯示的文字（可含 HTML）
     * @param string $content 點擊後顯示的 HTML 內容
     * @param array  $options 設定選項
     */
    public static function reveal(string $label, string $content, array $options = []): string
    {
        $escape = $options['escape'] ?? false;
        $finalContent = $escape ? self::escapeText($content) : $content;

        $id = self::generateId();
        $hidden = "<div id=\"{$id}\" style=\"display:none\">{$finalContent}</div>";

        $attrs = self::buildAttrs(array_merge($options, ['source' => $id]));
        $tag = self::wrapCard($label, $attrs, $options);

        return $tag . "\n" . $hidden;
    }

    /**
     * 揭露模式（純文字版）：適合直接傳 DB 欄位，自動跳脫與換行
     *
     * @param string $label   卡片上顯示的文字
     * @param string $content 純文字內容（\n 自動轉 <br>）
     * @param array  $options 設定選項
     */
    public static function revealText(string $label, string $content, array $options = []): string
    {
        return self::reveal($label, self::escapeText($content), $options);
    }

    /**
     * 揭露模式（關聯陣列版）：適合直接傳 DB row，自動產生定義清單
     * 每個 key/value 自動跳脫，\n 自動轉 <br>
     *
     * @param string $label   卡片上顯示的文字
     * @param array  $row     關聯陣列，key=欄位名，value=純文字值
     * @param array  $options 設定選項
     */
    public static function revealArray(string $label, array $row, array $options = []): string
    {
        $color    = $options['color'] ?? 'info';
        $keyColor = self::colorHex($color);

        $html = '<dl style="margin:0;display:grid;grid-template-columns:auto 1fr;gap:4px 16px">';
        foreach ($row as $key => $value) {
            $k = htmlspecialchars((string)$key,   ENT_QUOTES, 'UTF-8');
            $v = self::escapeText((string)$value);
            $html .= "<dt style=\"color:{$keyColor};font-weight:600;white-space:nowrap\">{$k}</dt>"
                   . "<dd style=\"margin:0\">{$v}</dd>";
        }
        $html .= '</dl>';

        return self::reveal($label, $html, $options);
    }

    /**
     * 測驗模式：點擊後出現輸入框，答對顯示解說內容
     *
     * @param string $question    題目文字（顯示在卡片上，點擊後也顯示在輸入框上方）
     * @param string $answer      正確答案
     * @param string $successHtml 答對後顯示的 HTML 內容（可為空字串，改用 success_message）
     * @param array  $options     設定選項
     */
    public static function quiz(
        string $question,
        string $answer,
        string $successHtml = '',
        array  $options = []
    ): string {
        $escape         = $options['escape'] ?? false;
        $successContent = $escape ? self::escapeText($successHtml) : $successHtml;

        // 答對後顯示的 DOM 元素
        $successSourceAttr = '';
        if ($successContent !== '') {
            $sid = self::generateId();
            $hidden = "<div id=\"{$sid}\" style=\"display:none\">{$successContent}</div>";
            $successSourceAttr = $sid;
        } else {
            $hidden = '';
        }

        $attrs = self::buildAttrs(array_merge($options, [
            'question'       => $question,
            'answer'         => $answer,
            'success_source' => $successSourceAttr,
        ]));

        $tag = self::wrapCard($question, $attrs, $options);
        return $tag . ($hidden ? "\n" . $hidden : '');
    }

    /**
     * 簡單卡片：不含點擊行為，純展示用
     * 適合在 groupOpen/groupClose 裡放靜態內容
     *
     * @param string $content HTML 內容
     * @param array  $options 設定選項
     */
    public static function card(string $content, array $options = []): string
    {
        $escape = $options['escape'] ?? false;
        $body   = $escape ? self::escapeText($content) : $content;
        $attrs  = self::buildAttrs($options);
        return self::wrapCard($body, $attrs, $options);
    }

    /**
     * 群組開始標籤
     *
     * @param array $options mode(stack|slide), theme, show_pages, hide_nav_buttons
     */
    public static function groupOpen(array $options = []): string
    {
        $mode    = $options['mode']  ?? 'stack';
        $theme   = $options['theme'] ?? '';

        $attrs = ' mode="' . htmlspecialchars($mode, ENT_QUOTES, 'UTF-8') . '"';
        if ($theme) {
            $attrs .= ' theme="' . htmlspecialchars($theme, ENT_QUOTES, 'UTF-8') . '"';
        }
        if (!empty($options['show_pages'])) {
            $attrs .= ' show-pages';
        }
        if (!empty($options['hide_nav_buttons'])) {
            $attrs .= ' hide-nav-buttons';
        }

        return "<card-toggle-group{$attrs}>";
    }

    /**
     * 群組結束標籤
     */
    public static function groupClose(): string
    {
        return '</card-toggle-group>';
    }

    /**
     * 輸出引入 script 標籤，安全呼叫多次只輸出一次
     *
     * @param string $jsPath card-toggle.js 的路徑
     */
    public static function script(string $jsPath): string
    {
        if (self::$scriptRendered) return '';
        self::$scriptRendered = true;
        return '<script src="' . htmlspecialchars($jsPath, ENT_QUOTES, 'UTF-8') . '"></script>' . "\n";
    }

    /**
     * 重置旗標（測試用途）
     */
    public static function reset(): void
    {
        self::$scriptRendered = false;
        self::$usedIds        = [];
    }

    // ──────────────────────────────────────────────
    //  私有輔助方法
    // ──────────────────────────────────────────────

    /**
     * 產生 card-toggle 元素的 HTML 屬性字串
     */
    private static function buildAttrs(array $options): string
    {
        $attrs = '';

        // 內容來源屬性（三擇一）
        if (!empty($options['source'])) {
            $attrs .= ' source="' . htmlspecialchars($options['source'], ENT_QUOTES, 'UTF-8') . '"';
        }
        if (!empty($options['content'])) {
            $attrs .= ' content="' . htmlspecialchars($options['content'], ENT_QUOTES, 'UTF-8') . '"';
        }
        if (!empty($options['question'])) {
            $attrs .= ' question="' . htmlspecialchars($options['question'], ENT_QUOTES, 'UTF-8') . '"';
        }

        // 外觀
        $color = $options['color'] ?? '';
        if ($color && in_array($color, self::VALID_COLORS, true)) {
            $attrs .= ' color="' . $color . '"';
        }
        $colorAfter = $options['color_after'] ?? '';
        if ($colorAfter && in_array($colorAfter, self::VALID_COLORS, true)) {
            $attrs .= ' color-after="' . $colorAfter . '"';
        }
        if (!empty($options['dashed'])) {
            $attrs .= ' dashed';
        }
        $size = $options['size'] ?? '';
        if ($size && in_array($size, self::VALID_SIZES, true)) {
            $attrs .= ' size="' . $size . '"';
        }
        $animation = $options['animation'] ?? '';
        if ($animation) {
            $attrs .= ' animation="' . htmlspecialchars($animation, ENT_QUOTES, 'UTF-8') . '"';
        }

        // 序號
        $number = $options['number'] ?? '';
        if ($number !== '') {
            $attrs .= ' number="' . htmlspecialchars((string)$number, ENT_QUOTES, 'UTF-8') . '"';
            $numberSize = $options['number_size'] ?? '';
            if ($numberSize) {
                $attrs .= ' number-size="' . htmlspecialchars($numberSize, ENT_QUOTES, 'UTF-8') . '"';
            }
        }

        // Quiz 專屬
        $answer = $options['answer'] ?? '';
        if ($answer !== '') {
            $attrs .= ' answer="' . htmlspecialchars($answer, ENT_QUOTES, 'UTF-8') . '"';
        }
        if (!empty($options['case_sensitive'])) {
            $attrs .= ' case-sensitive';
        }
        $maxAttempts = $options['max_attempts'] ?? '';
        if ($maxAttempts !== '') {
            $attrs .= ' max-attempts="' . (int)$maxAttempts . '"';
        }
        $placeholder = $options['placeholder'] ?? '';
        if ($placeholder) {
            $attrs .= ' placeholder="' . htmlspecialchars($placeholder, ENT_QUOTES, 'UTF-8') . '"';
        }
        $hint = $options['hint'] ?? '';
        if ($hint) {
            $attrs .= ' hint="' . htmlspecialchars($hint, ENT_QUOTES, 'UTF-8') . '"';
        }
        $hintTarget = $options['hint_target'] ?? '';
        if ($hintTarget) {
            $attrs .= ' hint-target="' . htmlspecialchars($hintTarget, ENT_QUOTES, 'UTF-8') . '"';
        }
        $submitText = $options['submit_text'] ?? '';
        if ($submitText) {
            $attrs .= ' submit-text="' . htmlspecialchars($submitText, ENT_QUOTES, 'UTF-8') . '"';
        }
        $hintText = $options['hint_text'] ?? '';
        if ($hintText) {
            $attrs .= ' hint-text="' . htmlspecialchars($hintText, ENT_QUOTES, 'UTF-8') . '"';
        }
        $resetText = $options['reset_text'] ?? '';
        if ($resetText) {
            $attrs .= ' reset-text="' . htmlspecialchars($resetText, ENT_QUOTES, 'UTF-8') . '"';
        }
        $successMsg = $options['success_message'] ?? '';
        if ($successMsg) {
            $attrs .= ' success-message="' . htmlspecialchars($successMsg, ENT_QUOTES, 'UTF-8') . '"';
        }
        $errorMsg = $options['error_message'] ?? '';
        if ($errorMsg) {
            $attrs .= ' error-message="' . htmlspecialchars($errorMsg, ENT_QUOTES, 'UTF-8') . '"';
        }
        $successSource = $options['success_source'] ?? '';
        if ($successSource) {
            $attrs .= ' success-source="' . htmlspecialchars($successSource, ENT_QUOTES, 'UTF-8') . '"';
        }
        $successColor = $options['success_color'] ?? '';
        if ($successColor && in_array($successColor, self::VALID_COLORS, true)) {
            $attrs .= ' success-color="' . $successColor . '"';
        }
        if (!empty($options['auto_next'])) {
            $attrs .= ' auto-next';
        }
        if (!empty($options['hide_submit'])) {
            $attrs .= ' hide-submit';
        }
        if (!empty($options['hide_hint'])) {
            $attrs .= ' hide-hint';
        }
        if (!empty($options['hide_reset'])) {
            $attrs .= ' hide-reset';
        }

        // 自訂 class
        $extraClass = $options['class'] ?? '';
        if ($extraClass) {
            $attrs .= ' class="' . htmlspecialchars($extraClass, ENT_QUOTES, 'UTF-8') . '"';
        }

        return $attrs;
    }

    /**
     * 包裝成 <card-toggle> 元素
     */
    private static function wrapCard(string $body, string $attrs, array $options): string
    {
        return "<card-toggle{$attrs}>{$body}</card-toggle>";
    }

    /**
     * 純文字跳脫：先 htmlspecialchars 再 nl2br
     */
    private static function escapeText(string $text): string
    {
        return nl2br(htmlspecialchars($text, ENT_QUOTES, 'UTF-8'));
    }

    /**
     * 產生不重複的短 id
     */
    private static function generateId(): string
    {
        do {
            $id = 'ct-' . substr(bin2hex(random_bytes(4)), 0, 6);
        } while (in_array($id, self::$usedIds, true));
        self::$usedIds[] = $id;
        return $id;
    }

    /**
     * 依顏色名稱回傳對應 hex，用於 revealArray 的 key 顏色
     */
    public static function colorHex(string $color): string
    {
        $map = [
            'safe'      => '#40c99a',
            'warning'   => '#F08080',
            'info'      => '#5fafed',
            'special'   => '#C8DD5A',
            'sky'       => '#08a9d1',
            'lavender'  => '#C3A5E5',
            'attention' => '#DECA4B',
            'salmon'    => '#E5C3B3',
            'brown'     => '#d9c5b2',
            'shell'     => '#c6c7bd',
            'pink'      => '#FFB3D9',
            'orange'    => '#eda109',
            'stone'     => '#7090A8',
        ];
        return $map[$color] ?? '#c6c7bd';
    }
}
