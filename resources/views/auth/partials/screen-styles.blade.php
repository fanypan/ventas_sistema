        <style>
            :root {
                --auth-ink: #0f172a;
                --auth-muted: #cbd5e1;
                --auth-panel: #f8fafc;
                --auth-line: #e2e8f0;
                --auth-accent: #4f46e5;
                --auth-accent-hover: #4338ca;
                --auth-brand: #0f172a;
                --auth-field-bg: #fff;
                --auth-lead: #475569;
                --auth-icon: #64748b;
                color-scheme: light;
            }
            @media (prefers-color-scheme: dark) {
                :root {
                    --auth-ink: #e2e8f0;
                    --auth-panel: #0b1220;
                    --auth-line: #334155;
                    --auth-field-bg: #1e293b;
                    --auth-lead: #94a3b8;
                    --auth-icon: #94a3b8;
                    color-scheme: dark;
                }
            }
            * { box-sizing: border-box; }
            html, body {
                min-height: 100%;
            }
            body.auth-screen {
                margin: 0;
                font-family: Outfit, system-ui, sans-serif;
                color: var(--auth-ink);
                background: var(--auth-panel);
                caret-color: var(--auth-accent);
            }
            .auth-screen ::selection {
                background: var(--auth-accent);
                color: #fff;
            }
            .auth-split {
                display: grid;
                grid-template-columns: minmax(0, 1fr) minmax(28rem, 40rem);
                min-height: 100vh;
                min-height: 100dvh;
            }
            .auth-brand {
                background: var(--auth-brand);
                color: #fff;
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                padding: 3.5rem 4rem;
            }
            .auth-brand-copy {
                max-width: 36rem;
                margin-top: auto;
                margin-bottom: auto;
            }
            .auth-brand-logo {
                max-width: 8.5rem;
                max-height: 5.5rem;
                width: auto;
                height: auto;
                object-fit: contain;
                margin-bottom: 2rem;
                filter: brightness(0) invert(1);
            }
            .auth-brand h1 {
                margin: 0 0 0.85rem;
                font-size: 3rem;
                font-weight: 700;
                letter-spacing: -0.03em;
                line-height: 1.05;
            }
            .auth-brand p {
                margin: 0;
                font-size: 1.2rem;
                line-height: 1.5;
                color: var(--auth-muted);
                max-width: 28rem;
            }
            .auth-brand-foot {
                font-size: 0.9rem;
                color: #94a3b8;
            }
            .auth-main {
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 3.5rem 3.25rem;
                background: var(--auth-panel);
            }
            .auth-form {
                width: 100%;
                max-width: 26rem;
                animation: auth-in 420ms cubic-bezier(0.16, 1, 0.3, 1) both;
            }
            @keyframes auth-in {
                from { opacity: 0; transform: translateY(10px); }
                to { opacity: 1; transform: none; }
            }
            .auth-form h2 {
                margin: 0 0 0.4rem;
                font-size: 1.85rem;
                font-weight: 700;
                letter-spacing: -0.03em;
                line-height: 1.15;
            }
            .auth-form .auth-lead {
                margin: 0 0 2rem;
                color: var(--auth-lead);
                font-size: 1.05rem;
            }
            .auth-field {
                margin-bottom: 1.15rem;
            }
            .auth-field label {
                display: block;
                margin-bottom: 0.4rem;
                font-size: 0.92rem;
                font-weight: 600;
            }
            .auth-field .input-group .form-control {
                height: 3.15rem;
                font-size: 1.05rem;
                border-radius: 0.6rem 0 0 0.6rem;
                border: 1px solid var(--auth-line);
                padding: 0.65rem 1rem;
                background: var(--auth-field-bg);
                color: var(--auth-ink);
            }
            .auth-field .input-group-text {
                min-width: 3.15rem;
                justify-content: center;
                background: var(--auth-field-bg);
                border: 1px solid var(--auth-line);
                border-left: 0;
                color: var(--auth-icon);
                border-radius: 0 0.6rem 0.6rem 0;
            }
            .auth-field button.input-group-text.password-toggle-btn {
                cursor: pointer;
                padding: 0;
            }
            .auth-field button.input-group-text.password-toggle-btn:hover,
            .auth-field button.input-group-text.password-toggle-btn:focus-visible {
                color: var(--auth-accent);
            }
            .auth-field .form-control:focus {
                border-color: var(--auth-accent);
                box-shadow: none;
            }
            .auth-field .form-control:focus + .input-group-append .input-group-text {
                border-color: var(--auth-accent);
                color: var(--auth-accent);
            }
            .auth-row {
                display: flex;
                align-items: center;
                justify-content: space-between;
                margin: 0.25rem 0 1.6rem;
            }
            .auth-row label {
                font-weight: 500;
                font-size: 0.95rem;
                margin: 0;
            }
            .auth-submit {
                display: block;
                width: 100%;
                height: 3.25rem;
                border: 0;
                border-radius: 0.6rem;
                background: var(--auth-accent);
                color: #fff;
                font-family: inherit;
                font-size: 1.12rem;
                font-weight: 600;
                letter-spacing: 0;
                white-space: nowrap;
                cursor: pointer;
            }
            .auth-submit:hover,
            .auth-submit:focus {
                background: var(--auth-accent-hover);
                color: #fff;
            }
            .auth-submit:focus-visible,
            .auth-field .form-control:focus-visible,
            .auth-row input:focus-visible {
                outline: 2px solid var(--auth-accent);
                outline-offset: 3px;
            }
            .invalid-feedback {
                font-size: 0.88rem;
            }
            @media (max-width: 960px) {
                .auth-split {
                    grid-template-columns: 1fr;
                    min-height: auto;
                }
                .auth-brand {
                    padding: 1.75rem 1.5rem 1.5rem;
                    min-height: 0;
                }
                .auth-brand-copy {
                    margin: 0;
                }
                .auth-brand-logo {
                    max-width: 5.5rem;
                    max-height: 3.5rem;
                    margin-bottom: 1rem;
                }
                .auth-brand h1 {
                    font-size: 1.85rem;
                    margin-bottom: 0.4rem;
                }
                .auth-brand p {
                    font-size: 1rem;
                }
                .auth-brand-foot {
                    display: none;
                }
                .auth-main {
                    padding: 2rem 1.35rem 2.5rem;
                    align-items: flex-start;
                }
            }
        </style>
