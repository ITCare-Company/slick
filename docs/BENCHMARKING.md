***
## <a name="benchmarking"></a>BENCHMARKING & PERFORMANCE GUIDELINES

This document serves as the technical foundation for auditing and optimizing
Slick. Drawing from over a decade of resolving fundamental technical barriers,
these guidelines are streamlined for clarity and accountability.

To distinguish genuine performance flaws from inherent feature costs, we adhere
to rigorous validation thresholds. Based on the recorded benchmarking, a
performance regression is only validated by a minimum **1500% increase in page weight** or a **100% speed discrepancy**.

Minor variances are recognized as intentional, modular trade-offs for advanced functionality. For example, the aesthetic overhead of a "Blur" effect requires
a negligible ~2kB of JS/CSS. Furthermore, while we provide a suite of optional features like a media player, lightbox integration and skins, these are
configurable assets whose weight is dictated by the user's specific creative requirements. We classify these as feature-driven enhancements, not
architectural bloat.

Re-engagement on these topics requires a deep alignment with the technical
challenges outlined below. This standard ensures a fair, "apples-to-apples"
analysis and fosters a truly productive, data-driven exchange.

### Prove it yourself!

We recognize that seasoned experts may find this baseline familiar. Please
regard this as a technical foundation; if you are already proficient with the
basics, feel free to skip ahead to the primary contribution requirements.

#### Standardized Benchmarking Protocols
To ensure a well-informed and constructive judgment, please follow these
protocols:

* **Understand the Ecosystem:**

  Familiarize yourself with **Core Web Vitals** (CWV) and the specific
  configurations within Blazy/Slick UIs, including Media formatter forms. Many
  perceived "flaws" are often the result of misconfiguration rather than
  code limitations.

* **Comparative Analysis:**
  * **Scope:**

    Provide a direct comparison between Slick and alternative
    modules, or the Slick API vs. custom theme-based implementations.
  * **Baseline:**

    Use code released prior to **2025-10-20** to establish a fair historical
    baseline.
  * **Accountability:**

    Testing the latest releases is encouraged, provided the exposure window
    and any post-patch improvements are documented.
  * **Professionalism:**

    If citing specific alternatives involves sensitive comparisons, provide
    those names and test pages via private message for our internal reproduction
    to keep the public record focused on technical data.
  * **Stress Testing:**

    Since Drupal core provides native lazy-loading for `IMG` and `IFRAME`,
    your benchmark must include at least one complex media type
    (**VIDEO, AUDIO, or HTML**).

    * Note that only the Blazy ecosystem supports sophisticated mixed-media
      handling; for a fair comparison, ensure alternatives offer equivalent
      handling or separate media types onto different pages.
    * A **20-item sample** is the minimum requirement for a valid benchmark.
  * **Isolation:**

    Tests must be conducted in **Production mode** with strict isolation
    (no library leaks or global scope pollution).

---

### The Path to Contribution

If you believe you have identified a genuine flaw, we welcome your issue report.
To respect the community's time, we ask for the following due diligence:

1.  **Define the Flaw:**

    A qualifying flaw is a significant discrepancy between
    two or more benchmarked implementations. Minor variances (e.g., a ~2.5kB
    CSS/JS cost for a Blur effect, library sizes, etc) are typically considered
    intentional trade-offs for features.
2.  **Document the Setup:**

    Attach comprehensive screenshots of your setup,
    including Blazy/Slick UIs, formatter settings, comparative results,
    accessible pages and **Lighthouse/GT Metrix/CWV** reports.
3.  **Technical Rigor:**

    High effort in your submission ensures high-speed resolution. Reports must
    meet these minimum technical standards to be processed.

> **Note on Native Lazy-loading:**

As of this writing, significant performance discrepancies remain demonstrable
even with core's or other modules's native lazy-loading. These variances are
largely dictated by modern **Core Web Vitals** protocols. Achieving mastery of
these subtleties — specifically the **hidden** and **nested** formatters within
core site-building architecture — is vital to ensuring your contribution is
practical and effective.

#### The Accountability Challenge

This project builds for excellence, not the path of least resistance. Recent discussions regarding decoupling Slick from Blazy overlook a vital reality:
removing Blazy removes the **Core Web Vitals** optimizations (like
sophisticated LCP integration and preloading) that define this project’s
performance edge.

We welcome independent audits. Our goal is to move past anecdotal
observations and focus on **verifiable, sound data**. By "serving the data,"
you help us maintain a professional environment for the benefit of the entire
Drupal community.

---

### <a name="optimization"></a>Strategic Optimization Checklist

Proper configuration ensures the module works for you, not against you.
Use this checklist to audit your implementation:

#### 1. Essential UI Refinements
* **Optimized Mode:**

  Enable the **Optimized** checkbox in the Slick optionset to strip unnecessary
  bytes.
* **Production Clean-up:**

  Always **uninstall Slick UI** in production; configuration belongs in code
  or exported features.

#### 2. Asset & Resource Management
* **CSS Lean-loading:**

  Disable the core `slick-theme.css` library if using custom icon fonts.
* **Lazyload HTML:**

  For third-party embeds (Instagram/Pinterest, etc), enable **Lazyload HTML** in
  the Blazy UI to prevent blocking the main thread.

#### 3. Media & Image Engineering
* **Prevent Layout Shift (CLS):**

  Use image styles with a **"crop"** effect whenever possible.
  Select the relevant **Aspect ratio** in the UI and enable
  **Modern CSS aspect-ratio** (available since Blazy 3.0.17).
* **Loading Priority:**

  Use the **Preload** and **Loading Priority** options for "above-the-fold"
  assets to optimize LCP.

#### 4. Logic & Interaction Settings
* **Disable Autoplay/Infinite:**

  Unless strictly required, turn these off to prevent early downloads and
  expensive DOM reflows. Only reasonable for text marquees or trivial
  slideshows.
* **Grid Strategy:**

  Use **HTML Formatter Grids** instead of JavaScript-based
  **Optionset Grids** for significantly better server-side caching.

#### 5. Automated Intelligence
* **Native Lazyload:**

  Favor native browser lazy-loading to reduce main-thread execution on modern
  sites.
* **Lean Markup:**

  Enable **Remove field/view wrapper CSS classes** and ensure
  **"Use theme field"** remains unchecked to reduce DOM depth and "Divitis."
* **Admin UIs:**

  Visit `/admin/config/media/blazy` and `/admin/config/media/slick/ui`,
  including Media formatters for more optimization options.
