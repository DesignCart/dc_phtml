<h2>Design Cart pHTML (dc_phtml)</h2>
<p>Design Cart pHTML is a PrestaShop 8/9 module that allows you to create and display multiple custom HTML content blocks with full control over styling and placement.</p>
<p>👉 For a full guide, see: <strong><a href="https://www.designcart.pl/laboratorium/309-jak-wstawic-wlasny-html-na-stronie-prestashop-darmowy-modul-z-edytorem-i-stylowaniem.html">how to add custom HTML in PrestaShop with a free module</a></strong></p>

<h2>1. Identity &amp; Compatibility</h2>
<ul>
<li><strong>Display name:</strong> Design Cart pHTML</li>
<li><strong>Technical name:</strong> dc_phtml</li>
<li><strong>Author:</strong> Design Cart</li>
<li><strong>Version:</strong> 1.1.0</li>
<li><strong>Platform:</strong> PrestaShop 8.x / 9.x
<ul>
<li><code>ps_versions_compliancy</code>: min. 8.0.0, max. current shop version</li>
</ul>
</li>
<li><strong>Category:</strong> front_office_features</li>
<li><strong>Interface:</strong> Implements <code>WidgetInterface</code> (rendered as a widget in hooks)</li>
</ul>

<h2>2. Purpose</h2>
<p>The module is designed to display custom content blocks on the storefront, each consisting of:</p>
<ul>
<li>a <strong>title</strong> (HTML tags h1&ndash;h6),</li>
<li>a <strong>content area</strong> (plain text or HTML via WYSIWYG editor),</li>
</ul>
<p>Each block has independent <strong>styling options</strong>, such as:</p>
<ul>
<li>background,</li>
<li>typography,</li>
<li>alignment,</li>
<li>uppercase transformations.</li>
</ul>
<p>All data is stored in the database as <strong>independent blocks</strong>, not as a single global configuration.</p>

<h2>3. Data Architecture (Database)</h2>
<p>On installation, the module creates the following tables (with shop prefix, e.g. <code>ps_</code>):</p>
<ul>
<li><strong>dc_phtml_block</strong><br/> Stores language-independent block settings:
<ul>
<li><code>id_shop</code>,</li>
<li>title tag,</li>
<li>colors,</li>
<li>font sizes,</li>
<li>switches and styling options.</li>
</ul>
</li>
<li><strong>dc_phtml_block_lang</strong><br/> Stores multilingual content:
<ul>
<li><code>title</code>,</li>
<li><code>content</code> (HTML),<br/> per <code>id_lang</code>.</li>
</ul>
</li>
<li>
<p><strong>dc_phtml_hook_instance</strong><br/> Assigns blocks to specific hook instances:</p>
<ul>
<li><code>(id_hook, id_shop, instance_position) &rarr; id_block</code></li>
</ul>
<p><code>instance_position</code> represents the execution order of the module on a given hook.</p>
</li>
</ul>
<h3>Installation</h3>
<ul>
<li>Creates a default block with sample title and content in all languages</li>
<li>Assigns it to <code>displayHome</code> as instance 1 (if the hook exists)</li>
</ul>
<h3>Uninstallation</h3>
<ul>
<li>Removes all tables</li>
<li>All blocks and assignments are permanently deleted</li>
</ul>
<h2>4. Admin Panel (Configuration)</h2>
<p>Configuration screen:<br/> <strong>Modules &rarr; Design Cart pHTML &rarr; Configure</strong></p>
<h3>A) Block List</h3>
<p>Table with:</p>
<ul>
<li>ID,</li>
<li>title (current BO language),</li>
<li>actions.</li>
</ul>
<p>Available actions:</p>
<ul>
<li><strong>Add block</strong> &ndash; open new block form</li>
<li><strong>Edit</strong> &ndash; modify existing block</li>
<li><strong>Delete</strong> &ndash; remove block (with confirmation)</li>
</ul>
<p>Deleting a block also removes its assignments from <code>dc_phtml_hook_instance</code>.</p>
<h3>B) Block Form</h3>
<p>Two separate sections (HelperForm):</p>
<h4>Content</h4>
<ul>
<li>Multilingual title</li>
<li>Heading tag (h1&ndash;h6)</li>
<li>Content (WYSIWYG TinyMCE, HTML allowed with <code>isCleanHtml</code> validation)</li>
</ul>
<h4>Appearance</h4>
<ul>
<li>Background (CSS value)</li>
<li>Title: font size, color, weight (200&ndash;900), uppercase, alignment</li>
<li>Content: font size, color, centered option, uppercase</li>
<li>Save button</li>
<li>Optional: back to list</li>
</ul>
<h3>C) Hook Assignments</h3>
<ul>
<li>Each module call on a hook is treated as a separate <strong>instance</strong> (1, 2, 3&hellip;)</li>
<li>For each hook, you can assign a block to each instance</li>
<li>Dropdown selection (or &ldquo;none&rdquo;)</li>
<li>Save via <strong>Save assignments</strong></li>
</ul>
<h2>5. Front Office</h2>
<ul>
<li>Template: <code>views/templates/hook/dc_phtml.tpl</code></li>
</ul>
<p>Output structure:</p>
<ul>
<li>Section with inline background styles</li>
<li>Bootstrap layout (<code>container / row / col-12</code>)</li>
<li>Title rendered as h1&ndash;h6 with inline styles</li>
<li>Content inside a styled <code>div</code></li>
<li>HTML is supported (<code>nofilter</code> where applicable)</li>
<li>Language: based on frontend context (<code>id_lang</code>)</li>
<li>Cache key: <code>dc_phtml_{id_block}</code></li>
</ul>
<h2>6. Hooks &amp; Registration</h2>
<ul>
<li>Automatically registers to <code>displayHome</code> during installation</li>
<li>Can be attached to other hooks via PrestaShop module positions</li>
</ul>
<h2>7. Platform Limitations (Multiple Instances)</h2>
<ul>
<li>PrestaShop core often limits one module instance per hook</li>
<li>You may not be able to add the same module twice to the same hook</li>
</ul>
<p>This is a platform limitation, not a module issue.</p>
<p>The module still supports:</p>
<ul>
<li>multiple content blocks</li>
<li>assigning different blocks to a single hook instance</li>
</ul>
<p>Multiple instance logic applies mainly when the engine calls the module multiple times (custom setups or modified core).</p>
<h2>8. Security &amp; Content</h2>
<ul>
<li>HTML content is validated using <code>isCleanHtml</code></li>
<li>Elements like iframes may require global shop configuration</li>
<li>Admin actions use tokens and shop context for security</li>
</ul>
<h2>9. Summary</h2>
<p>Design Cart pHTML is a PrestaShop 8/9 module for creating multiple HTML content blocks with full styling control, multilingual support, database storage, and hook-based placement&mdash;while multiple instances per hook depend on PrestaShop core limitations. 🚀</p>
