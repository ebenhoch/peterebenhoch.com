# Contact Form Design Proposal

## Overview
Design proposal for integrating a contact form into `contact.qmd` that collects email addresses and interests, with future integration to Attio CRM API.

## Form Layout Design

### Option A: Two-Column Layout (Recommended)
Matches the existing grid layout style used throughout the site.

```
┌─────────────────────────────────────────────────────────┐
│  Contact Form                                           │
├─────────────────────────────────────────────────────────┤
│                                                          │
│  Email Address *                                        │
│  [_____________________________]                        │
│                                                          │
│  I'm interested in: *                                   │
│                                                          │
│  ┌──────────────┐  ┌──────────────┐                    │
│  │ Skills       │  │ Sectors      │                    │
│  ├──────────────┤  ├──────────────┤                    │
│  │ ☐ Data       │  │ ☐ Engineering│                    │
│  │   Governance │  │ ☐ Healthcare │                    │
│  │ ☐ Data       │  │ ☐ Media      │                    │
│  │   Privacy    │  │ ☐ Software   │                    │
│  │ ☐ Information│  │ ☐ Telekoms   │                    │
│  │   Security   │  │ ☐ Transport  │                    │
│  │ ☐ Legal      │  │ ☐ Utilities  │                    │
│  │   Counseling │  │              │                    │
│  │ ☐ Software   │  │              │                    │
│  │   Operations │  │              │                    │
│  │ ☐ Thinking & │  │              │                    │
│  │   Analysis   │  │              │                    │
│  └──────────────┘  └──────────────┘                    │
│                                                          │
│  [Submit]                                               │
│                                                          │
└─────────────────────────────────────────────────────────┘
```

### Option B: Single Column with Grouped Sections
More mobile-friendly, vertical layout.

### Option C: Accordion/Collapsible Sections
Space-saving, modern approach.

## Interest Categories

### Skills (7 options)
1. Data Governance
2. Data Privacy
3. Information Security
4. Legal Counseling
5. Software Operations
6. Thinking & Analysis

### Sectors (7 options)
1. Engineering
2. Healthcare
3. Media
4. Software
5. Telekoms
6. Transport
7. Utilities

### Additional Options
- General Inquiry
- Newsletter/Updates

## HTML Structure Proposal

```html
<form id="contact-form" action="/api/submit-contact" method="POST">
  <!-- Email Field -->
  <div class="mb-3">
    <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
    <input type="email" class="form-control" id="email" name="email" required>
    <div class="invalid-feedback">Please provide a valid email address.</div>
  </div>

  <!-- Interests Section -->
  <div class="mb-3">
    <label class="form-label">I'm interested in: <span class="text-danger">*</span></label>
    
    <div class="row">
      <!-- Skills Column -->
      <div class="col-md-6">
        <h5>Skills</h5>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" name="interests[]" value="data-governance" id="interest-dg">
          <label class="form-check-label" for="interest-dg">Data Governance</label>
        </div>
        <!-- ... more checkboxes ... -->
      </div>
      
      <!-- Sectors Column -->
      <div class="col-md-6">
        <h5>Sectors</h5>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" name="interests[]" value="engineering" id="interest-eng">
          <label class="form-check-label" for="interest-eng">Engineering</label>
        </div>
        <!-- ... more checkboxes ... -->
      </div>
    </div>
  </div>

  <!-- Submit Button -->
  <button type="submit" class="btn btn-primary">Submit</button>
</form>
```

## Integration into contact.qmd

The form should be added below the existing contact information, maintaining the grid layout:

```markdown
::: {.grid}

::: {.g-col-6}
![](logo-paloalto.png)
:::

::: {.g-col-6}
Dr. Peter Ebenhoch
pe@peterebenhoch.com
https://linkedin.com/in/ebenhoch
+41 78 932 19 19
:::

:::

---

## Contact Form

[Form HTML here]

---
```

## Styling Considerations

- Use Bootstrap form classes (already available in Quarto)
- Match existing color scheme (navbar: #8C1515)
- Responsive design (mobile-friendly)
- Accessible labels and ARIA attributes
- Visual feedback on submission (success/error messages)

## JavaScript Validation

- Client-side email validation
- Ensure at least one interest is selected
- Show loading state during submission
- Display success/error messages
- Prevent double-submission

## Next Steps

1. Choose layout option (A, B, or C)
2. Finalize interest categories
3. Implement form HTML in contact.qmd
4. Add JavaScript for validation and submission
5. Create backend API endpoint



