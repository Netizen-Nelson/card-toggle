# card-toggle + CardToggle.php

> An interactive card component with click-to-reveal, quiz mode, and group carousel,
> paired with a PHP class that renders directly from database data.

![No Dependencies](https://img.shields.io/badge/dependencies-Bootstrap_Icons_only-40c99a?style=flat-square)
![PHP](https://img.shields.io/badge/PHP-8.0%2B-C3A5E5?style=flat-square&logo=php)
![License](https://img.shields.io/badge/license-MIT-08a9d1?style=flat-square)
![Custom Elements](https://img.shields.io/badge/Web_Components-Custom_Elements-C8DD5A?style=flat-square)

---

## 🆕 What's New

Three new attributes have been added to `<card-toggle>`.

### `auto-flip="ms"`

After the card is flipped to its back face, it automatically flips back to the front after the specified number of milliseconds. A countdown progress bar is shown at the bottom of the card during the wait.

```html
<!-- Flip back after 3 seconds -->
<card-toggle content="Back content" auto-flip="3000" color="info">
  Click me — flips back in 3 s
</card-toggle>
```

### `toggle`

Boolean attribute. When present, clicking anywhere on the back face flips the card back to the front. A subtle "↺ 點擊翻回" hint label appears on the back face.

```html
<card-toggle content="Back content" toggle color="lavender">
  Click to flip — click back face to return
</card-toggle>
```

### `flip-bar-color="name"`

Sets the colour of the `auto-flip` countdown bar independently from the card colour. Accepts any built-in colour name or a raw CSS colour value (hex, rgb, etc.).

**Colour resolution priority (highest → lowest):**

```
flip-bar-color  →  color-after  →  color  →  sky (default)
```

```html
<!-- Bar colour follows color-after automatically -->
<card-toggle auto-flip="4000" color-after="safe" content="Back">Front</card-toggle>

<!-- Bar colour explicitly overridden -->
<card-toggle auto-flip="4000" color="lavender" flip-bar-color="warning" content="Back">Front</card-toggle>

<!-- Raw CSS value also accepted -->
<card-toggle auto-flip="4000" flip-bar-color="#ff6600" content="Back">Front</card-toggle>
```

### Combining `auto-flip` and `toggle`

Both attributes can be used together. While the countdown is running, clicking the back face cancels the timer and flips back immediately.

```html
<card-toggle
  content="Back content"
  auto-flip="5000"
  toggle
  color="safe">
  5 s auto-flip, or click back face to return early
</card-toggle>
```

### Bug fix — blank front face after flip-back

The front-face snapshot is now taken **lazily on first click** (instead of in `connectedCallback`), guaranteeing that child nodes are fully parsed in all browsers (including Firefox and Safari) before the snapshot is recorded. The front face will no longer appear blank after flipping back.

---

## What is this?

**card-toggle** is a Web Component (`<card-toggle>`) that transforms static cards into
interactive elements. Three modes are supported:

- **Reveal** — click to replace card content with any HTML
- **Quiz** — click to show an answer input; validates against a correct answer with feedback
- **Group** — wrap multiple cards in `<card-toggle-group>` for stack or slide navigation

The companion **CardToggle.php** class renders card markup directly from PHP variables
or database query results, handling ID generation and HTML escaping automatically.

> Requires [Bootstrap Icons](https://icons.getbootstrap.com/) for button icons in quiz mode.
> Optimised for desktop / widescreen layouts.

---

## Files

```
your-project/
├── card-toggle.js      ← Web Component (CSS injected automatically)
├── CardToggle.php      ← PHP render class
└── your-page.php       ← your page, require CardToggle.php
```

---

## Table of Contents

1. [What's New](#-whats-new)
2. [Quick Start](#quick-start)
3. [HTML Usage](#html-usage)
   - [Reveal mode](#reveal-mode)
   - [Quiz mode](#quiz-mode)
   - [Group](#group)
4. [PHP Class Usage](#php-class-usage)
5. [Colors](#colors)
6. [Options Reference](#options-reference)
7. [Escape & Newline Handling](#escape--newline-handling)
8. [License](#license)

---

## Quick Start

```html
<!-- Bootstrap Icons (required for quiz buttons) -->
<link rel="stylesheet"
  href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<!-- Reveal: click to show content -->
<card-toggle color="info" source="detail-1">
  點擊查看詳情
</card-toggle>

<div id="detail-1" style="display:none">
  <p>這是揭露後的內容，可以是任意 HTML。</p>
</div>

<!-- Include script before </body> -->
<script src="card-toggle.js"></script>
```

```php
<?php
require_once 'CardToggle.php';

// Reveal from DB row
echo CardToggle::revealArray('王大明　點擊展開', [
    '部門' => $emp['dept'],
    '職稱' => $emp['title'],
    '過敏' => $emp['allergy'],
], ['color' => 'lavender']);

// Quiz from DB field
echo CardToggle::quiz(
    '阿斯匹靈最大單次劑量是多少 mg？',
    $drug['max_single'],
    '<p>正確！' . $drug['dosage'] . '</p>',
    ['color' => 'warning', 'max_attempts' => 3]
);

// Script tag (renders only once)
echo CardToggle::script('/assets/card-toggle.js');
?>
```

---

## HTML Usage

### Reveal mode

Three ways to provide content — choose one per card.

**`content` attribute** — inline HTML string (short content):

```html
<card-toggle color="safe" content="這是說明文字，可含 <b>HTML</b>">
  點擊查看
</card-toggle>
```

**`source` attribute** — point to a hidden element (large HTML blocks):

```html
<card-toggle color="info" source="my-detail" animation="slide">
  點擊查看詳情
</card-toggle>

<div id="my-detail" style="display:none">
  <table>...</table>
  <p>任意 HTML 內容</p>
</div>
```

**No attribute** — card body itself becomes the trigger; useful inside groups.

---

### Quiz mode

Add the `question` attribute to activate quiz mode.
Click the card to show an input field. The component validates the answer and shows feedback.

```html
<card-toggle
  color="warning"
  question="阿斯匹靈最大單次劑量是多少 mg？"
  answer="650"
  max-attempts="3"
  placeholder="輸入數字（mg）"
  hint="介於 325 到 650 之間"
  hint-target="hint-area"
  success-source="aspirin-answer"
  success-color="safe"
  error-message="答案不正確，請再想想"
  auto-next
  size="lg"
>
  阿斯匹靈最大單次劑量是多少 mg？
</card-toggle>

<!-- Shown after correct answer -->
<div id="aspirin-answer" style="display:none">
  <p>正確！標準劑量為 325–650 mg，每 4–6 小時服用一次。</p>
</div>

<!-- Hint display target -->
<div id="hint-area"></div>
```

---

### Group

Wrap cards in `<card-toggle-group>` for coordinated layout and navigation.

**Stack mode** (default) — vertical list:

```html
<card-toggle-group mode="stack" theme="sky">
  <card-toggle color="sky"  source="step-1">Step 1 確認病患身分</card-toggle>
  <card-toggle color="info" source="step-2">Step 2 評估生命徵象</card-toggle>
  <card-toggle color="safe" source="step-3">Step 3 執行醫囑</card-toggle>
</card-toggle-group>
```

**Slide mode** — horizontal carousel with prev/next buttons:

```html
<card-toggle-group mode="slide" theme="warning" show-pages>
  <card-toggle color="warning" question="題目一" answer="A">題目一</card-toggle>
  <card-toggle color="warning" question="題目二" answer="B">題目二</card-toggle>
  <card-toggle color="warning" question="題目三" answer="C">題目三</card-toggle>
</card-toggle-group>
```

---

## PHP Class Usage

### `CardToggle::reveal()`

Reveal mode with HTML content you assemble. `escape` defaults to `false`.

```php
$html = '<table>...' . nl2br(htmlspecialchars($row['note'], ENT_QUOTES, 'UTF-8')) . '</table>';

echo CardToggle::reveal(
    '點擊查看診斷結果',
    $html,
    ['color' => 'info', 'animation' => 'slide']
);
```

---

### `CardToggle::revealText()`

Reveal mode for plain-text DB fields. `escape` forced `true` — `htmlspecialchars` + `nl2br` applied automatically.

```php
echo CardToggle::revealText(
    'Step 1 確認病患身分　點擊展開',
    $sop['description'],    // plain text, \n auto → <br>
    ['color' => 'sky', 'number' => 1, 'animation' => 'slide']
);
```

---

### `CardToggle::revealArray()`

Pass a DB row directly. Each key becomes a label; each value is auto-escaped.
Outputs a definition-list layout — no HTML assembly needed.

```php
echo CardToggle::revealArray(
    '王大明　點擊展開',
    [
        '部門'     => $emp['dept'],
        '職稱'     => $emp['title'],
        '到職日'   => $emp['hire_date'],
        '藥物過敏' => $emp['allergy'],
        '考核評等' => $emp['grade'],
    ],
    ['color' => 'lavender', 'animation' => 'fade']
);
```

---

### `CardToggle::quiz()`

Quiz mode. Answer and success content come from DB fields.

```php
$successHtml = '<p>正確！標準劑量：' . htmlspecialchars($drug['dosage'], ENT_QUOTES, 'UTF-8') . '</p>';

echo CardToggle::quiz(
    $drug['name'] . ' 的最大單次劑量是多少 mg？',
    $drug['max_single'],
    $successHtml,
    [
        'color'         => 'warning',
        'max_attempts'  => 3,
        'placeholder'   => '輸入數字（mg）',
        'hint'          => $drug['dosage'],
        'hint_target'   => 'quiz-hint-area',
        'error_message' => '答案不正確，請再想想',
        'success_color' => 'safe',
        'auto_next'     => true,
        'submit_text'   => '確認答案',
        'size'          => 'lg',
    ]
);
```

---

### `CardToggle::card()`

Static card — no click behaviour. Use inside groups or for display-only content.

```php
echo CardToggle::card(
    '<strong>純展示卡片</strong>，不可點擊',
    ['color' => 'stone', 'size' => 'sm']
);
```

---

### `CardToggle::groupOpen()` / `groupClose()`

Wrap multiple cards in a group container.

```php
echo CardToggle::groupOpen([
    'mode'             => 'slide',
    'theme'            => 'warning',
    'show_pages'       => true,
    'hide_nav_buttons' => false,
]);

foreach ($questions as $q) {
    echo CardToggle::quiz($q['question'], $q['answer'], '', [
        'color'     => 'warning',
        'auto_next' => true,
    ]);
}

echo CardToggle::groupClose();
```

---

### `CardToggle::script()`

Outputs the `<script>` tag. Safe to call multiple times — renders only once.

```php
// Place before </body>
echo CardToggle::script('/assets/card-toggle.js');
```

---

## Colors

Set via `color` attribute (HTML) or `'color'` option (PHP).
Also used for `color-after` / `color_after`, `success-color` / `success_color`, and `flip-bar-color` / `flip_bar_color`.

| Name | Hex |
|---|---|
| `safe` | `#40c99a` |
| `warning` | `#F08080` |
| `info` | `#5fafed` |
| `special` | `#C8DD5A` |
| `sky` | `#08a9d1` |
| `lavender` | `#C3A5E5` |
| `attention` | `#DECA4B` |
| `salmon` | `#E5C3B3` |
| `pink` | `#FFB3D9` |
| `orange` | `#eda109` |
| `stone` | `#7090A8` |
| `brown` | `#d9c5b2` |
| `shell` | `#c6c7bd` |

Add `dashed` attribute / `'dashed' => true` option to switch to a dashed border
tinted with the active colour.

---

## Options Reference

### `<card-toggle>` attributes

| Attribute | Values | Default | Description |
|---|---|---|---|
| `source` | element id | — | Clone content from hidden element |
| `content` | HTML string | — | Inline content string |
| `question` | string | — | Activates quiz mode; shown as prompt |
| `answer` | string | — | Correct answer for quiz mode |
| `color` | colour name | — | Left accent bar colour |
| `color-after` | colour name | — | Left accent bar colour after reveal |
| `dashed` | boolean attr | — | Dashed border tinted with colour |
| `size` | `xsm` `sm` `lg` `xlg` | *(default)* | Card padding and font size |
| `animation` | `fade` `slide` `none` | `fade` | Reveal transition |
| `number` | string/int | — | Sequence label shown top-left |
| `number-size` | CSS value | `0.8rem` | Font size of number label |
| `case-sensitive` | boolean attr | — | Case-sensitive answer check |
| `max-attempts` | int | `999` | Max wrong attempts before lockout |
| `placeholder` | string | `請輸入答案` | Input placeholder text |
| `hint` | string | — | Hint text content |
| `hint-target` | element id | — | Where to display the hint |
| `submit-text` | string | `提交答案` | Submit button label |
| `hint-text` | string | `提示` | Hint button label |
| `reset-text` | string | `重置` | Reset button label |
| `hide-submit` | boolean attr | — | Hide submit button |
| `hide-hint` | boolean attr | — | Hide hint button |
| `hide-reset` | boolean attr | — | Hide reset button |
| `success-message` | string | `答對了！` | Text shown on correct answer (no `success-source`) |
| `error-message` | string | `答錯了，請再試一次` | Text shown on wrong answer |
| `success-source` | element id | — | Clone element shown after correct answer |
| `success-color` | colour name | `safe` | Accent bar colour after correct answer |
| `auto-next` | boolean attr | — | Auto-advance group to next card after correct answer |
| `auto-flip` 🆕 | ms (integer) | — | Flip back to front after N milliseconds; shows countdown bar |
| `toggle` 🆕 | boolean attr | — | Allow clicking back face to flip back to front |
| `flip-bar-color` 🆕 | colour name / CSS value | auto | Countdown bar colour; defaults to `color-after` → `color` → `sky` |

### `<card-toggle-group>` attributes

| Attribute | Values | Default | Description |
|---|---|---|---|
| `mode` | `stack` `slide` | `stack` | Layout mode |
| `theme` | colour name | `special` | Nav button active colour |
| `show-pages` | boolean attr | — | Show numbered page buttons |
| `hide-nav-buttons` | boolean attr | — | Hide prev/next nav buttons |

### PHP `$options` array keys

PHP option keys use underscores where HTML attributes use hyphens.

| Key | HTML equivalent | Type | Default |
|---|---|---|---|
| `color` | `color` | string | — |
| `color_after` | `color-after` | string | — |
| `dashed` | `dashed` | bool | `false` |
| `size` | `size` | string | — |
| `animation` | `animation` | string | `fade` |
| `number` | `number` | int/string | — |
| `number_size` | `number-size` | string | — |
| `answer` | `answer` | string | — |
| `case_sensitive` | `case-sensitive` | bool | `false` |
| `max_attempts` | `max-attempts` | int | `999` |
| `placeholder` | `placeholder` | string | — |
| `hint` | `hint` | string | — |
| `hint_target` | `hint-target` | string | — |
| `submit_text` | `submit-text` | string | — |
| `hint_text` | `hint-text` | string | — |
| `reset_text` | `reset-text` | string | — |
| `hide_submit` | `hide-submit` | bool | `false` |
| `hide_hint` | `hide-hint` | bool | `false` |
| `hide_reset` | `hide-reset` | bool | `false` |
| `success_message` | `success-message` | string | — |
| `error_message` | `error-message` | string | — |
| `success_source` | `success-source` | string | auto-generated |
| `success_color` | `success-color` | string | `safe` |
| `auto_next` | `auto-next` | bool | `false` |
| `escape` | — | bool | method-dependent |
| `class` | `class` | string | — |
| `auto_flip` 🆕 | `auto-flip` | int (ms) | — |
| `toggle` 🆕 | `toggle` | bool | `false` |
| `flip_bar_color` 🆕 | `flip-bar-color` | string | auto |

---

## Escape & Newline Handling

| Method | `escape` default | `\n` → `<br>` | Use when |
|---|---|---|---|
| `reveal()` | `false` | manual | You assemble the HTML yourself |
| `revealText()` | `true` (forced) | ✅ automatic | Plain-text DB field, single value |
| `revealArray()` | `true` (forced) | ✅ automatic | Dump a DB row directly, no HTML needed |
| `quiz()` | `false` | manual | Question and success content from DB |
| `card()` | `false` | manual | Static display card |

> **Security note:**
> When assembling HTML manually for `reveal()`, `quiz()`, or `card()`,
> always escape untrusted field values:
> ```php
> // Plain value
> htmlspecialchars($value, ENT_QUOTES, 'UTF-8')
>
> // Value with newlines
> nl2br(htmlspecialchars($value, ENT_QUOTES, 'UTF-8'))
> ```

---

## License

MIT License — free to use, modify, and distribute in personal and commercial projects.
