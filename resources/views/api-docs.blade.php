<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Memorial App API</title>
    <style>
        :root {
            color-scheme: light;
            --ink: #17211d;
            --muted: #5a6861;
            --line: #cbd8d2;
            --paper: #f6faf8;
            --white: #ffffff;
            --green: #227060;
            --green-soft: #d8eee8;
            --rose: #b94f61;
            --gold: #c49a2c;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: var(--paper);
            color: var(--ink);
        }

        a {
            color: inherit;
        }

        .shell {
            width: min(1120px, calc(100% - 32px));
            margin: 0 auto;
        }

        .masthead {
            display: grid;
            grid-template-columns: minmax(0, 1.1fr) minmax(260px, .9fr);
            min-height: 72vh;
            align-items: center;
            gap: 36px;
            padding: 36px 0 28px;
        }

        .eyebrow {
            color: var(--green);
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0;
            text-transform: uppercase;
        }

        h1 {
            margin: 14px 0 16px;
            max-width: 720px;
            font-size: clamp(38px, 7vw, 72px);
            line-height: .98;
            letter-spacing: 0;
        }

        .lead {
            max-width: 640px;
            color: var(--muted);
            font-size: 18px;
            line-height: 1.65;
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 28px;
        }

        .button {
            display: inline-flex;
            align-items: center;
            min-height: 44px;
            padding: 0 18px;
            border: 1px solid var(--green);
            border-radius: 8px;
            background: var(--green);
            color: var(--white);
            font-weight: 700;
            text-decoration: none;
        }

        .button.secondary {
            background: transparent;
            color: var(--green);
        }

        .photo {
            min-height: 430px;
            border-radius: 8px;
            background-image: linear-gradient(180deg, rgba(23, 33, 29, .08), rgba(23, 33, 29, .24)), url('https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=1200&q=80');
            background-size: cover;
            background-position: center;
            box-shadow: 0 18px 50px rgba(23, 33, 29, .16);
        }

        .status-band {
            border-top: 1px solid var(--line);
            border-bottom: 1px solid var(--line);
            background: var(--white);
        }

        .status-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1px;
        }

        .metric {
            padding: 22px 0;
        }

        .metric strong {
            display: block;
            font-size: 26px;
        }

        .metric span {
            color: var(--muted);
            font-size: 14px;
        }

        .section {
            padding: 54px 0;
        }

        .section-head {
            display: flex;
            align-items: end;
            justify-content: space-between;
            gap: 24px;
            margin-bottom: 20px;
        }

        h2 {
            margin: 0;
            font-size: 30px;
            letter-spacing: 0;
        }

        .hint {
            margin: 0;
            color: var(--muted);
            line-height: 1.5;
        }

        .endpoint-list {
            display: grid;
            gap: 12px;
        }

        .endpoint {
            display: grid;
            grid-template-columns: 92px minmax(0, 1fr);
            gap: 16px;
            align-items: start;
            padding: 18px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: var(--white);
        }

        .method {
            display: inline-flex;
            justify-content: center;
            min-width: 72px;
            padding: 7px 10px;
            border-radius: 8px;
            background: var(--green-soft);
            color: var(--green);
            font-size: 13px;
            font-weight: 800;
        }

        .method.post {
            background: #f6e9bd;
            color: #74520f;
        }

        .method.put {
            background: #e9def5;
            color: #654382;
        }

        .method.delete {
            background: #f7dce1;
            color: #8f2f40;
        }

        code {
            overflow-wrap: anywhere;
            font-family: Menlo, Monaco, Consolas, monospace;
            font-size: 14px;
        }

        .endpoint p {
            margin: 8px 0 0;
            color: var(--muted);
            line-height: 1.5;
        }

        .quick-test {
            display: grid;
            gap: 16px;
            padding: 20px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: #ffffff;
        }

        pre {
            margin: 0;
            overflow-x: auto;
            padding: 16px;
            border-radius: 8px;
            background: #17211d;
            color: #e7f3ef;
            line-height: 1.6;
        }

        .notice {
            border-left: 4px solid var(--gold);
            padding: 14px 16px;
            background: #fff8df;
            color: #4f421b;
        }

        footer {
            padding: 34px 0;
            border-top: 1px solid var(--line);
            color: var(--muted);
        }

        @media (max-width: 760px) {
            .masthead {
                grid-template-columns: 1fr;
                min-height: auto;
            }

            .photo {
                min-height: 260px;
            }

            .status-grid,
            .section-head {
                display: block;
            }

            .endpoint {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    @php
        $apiBase = url('/api');
        $featureEndpoints = [
            ['GET', '/features', 'List active features.'],
            ['POST', '/features', 'Create a feature with memorial text, memorial date, and one image.'],
            ['GET', '/features/trashed', 'List soft-deleted features.'],
            ['GET', '/features/{feature}', 'Show one active feature.'],
            ['PUT', '/features/{feature}', 'Update a feature.'],
            ['DELETE', '/features/{feature}', 'Soft-delete a feature.'],
            ['POST', '/features/{feature}/restore', 'Restore a soft-deleted feature.'],
            ['DELETE', '/features/{feature}/permanent', 'Permanently delete a soft-deleted feature.'],
        ];
        $collectionEndpoints = [
            ['GET', '/collections', 'List active collections with their features.'],
            ['POST', '/collections', 'Create a collection and image-backed features.'],
            ['GET', '/collections/{collection}', 'Show one active collection.'],
            ['PUT', '/collections/{collection}', 'Update a collection. Use multipart form data for images.'],
            ['DELETE', '/collections/{collection}', 'Soft-delete a collection.'],
            ['POST', '/collections/{collection}/restore', 'Restore a soft-deleted collection.'],
            ['DELETE', '/collections/{collection}/permanent', 'Permanently delete a soft-deleted collection.'],
        ];
    @endphp

    <main>
        <section class="shell masthead">
            <div>
                <div class="eyebrow">Memorial App API</div>
                <h1>Service is live. API routes start at <code>/api</code>.</h1>
                <p class="lead">
                    Use the feature and collection endpoints with the <code>X-API-KEY</code> header. Browser paths like <code>/features</code> open this guide; JSON endpoints live under <code>{{ $apiBase }}</code>.
                </p>
                <div class="actions">
                    <a class="button" href="#features">Feature Routes</a>
                    <a class="button secondary" href="#collections">Collection Routes</a>
                    <a class="button secondary" href="{{ url('/postman-collection') }}">Postman Collection</a>
                </div>
            </div>
            <div class="photo" role="img" aria-label="Quiet garden path at sunrise"></div>
        </section>

        <section class="status-band">
            <div class="shell status-grid">
                <div class="metric">
                    <strong>15</strong>
                    <span>API routes</span>
                </div>
                <div class="metric">
                    <strong>2</strong>
                    <span>resource groups</span>
                </div>
                <div class="metric">
                    <strong>Header</strong>
                    <span><code>X-API-KEY</code> required</span>
                </div>
            </div>
        </section>

        <section class="shell section">
            <div class="section-head">
                <div>
                    <h2>Quick Test</h2>
                    <p class="hint">A request without the API key returns <code>401 Unauthorized</code>.</p>
                </div>
            </div>
            <div class="quick-test">
                <pre><code>curl -H "Accept: application/json" \
  -H "X-API-KEY: your-api-key" \
  {{ $apiBase }}/features</code></pre>
                <div class="notice">
                    Store the deployed API key in Railway as <code>API_KEY</code>. Keep Postman and your app clients using the same value.
                </div>
            </div>
        </section>

        <section id="features" class="shell section">
            <div class="section-head">
                <div>
                    <h2>Feature Routes</h2>
                    <p class="hint">Memorial feature records with image upload support.</p>
                </div>
            </div>
            <div class="endpoint-list">
                @foreach ($featureEndpoints as [$method, $path, $description])
                    <article class="endpoint">
                        <span class="method {{ strtolower($method) }}">{{ $method }}</span>
                        <div>
                            <code>{{ $apiBase }}{{ $path }}</code>
                            <p>{{ $description }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        <section id="collections" class="shell section">
            <div class="section-head">
                <div>
                    <h2>Collection Routes</h2>
                    <p class="hint">Collections group multiple uploaded memorial images into feature records.</p>
                </div>
            </div>
            <div class="endpoint-list">
                @foreach ($collectionEndpoints as [$method, $path, $description])
                    <article class="endpoint">
                        <span class="method {{ strtolower($method) }}">{{ $method }}</span>
                        <div>
                            <code>{{ $apiBase }}{{ $path }}</code>
                            <p>{{ $description }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    </main>

    <footer>
        <div class="shell">
            Memorial App API. For JSON responses, request <code>/api/features</code> or <code>/api/collections</code>.
        </div>
    </footer>
</body>
</html>
