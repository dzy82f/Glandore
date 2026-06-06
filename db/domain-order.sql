START TRANSACTION;

UPDATE wp_sa_classes
SET
  class_title = 'Personal Effectiveness',
  sort_order = 10,
  status = 'active'
WHERE class_slug = 'personal-effectiveness';

UPDATE wp_sa_classes
SET
  class_title = 'AI & Organisational Intelligence',
  sort_order = 20,
  status = 'active'
WHERE class_slug IN ('ai', 'ai-organisational-intelligence');

UPDATE wp_sa_classes
SET sort_order = 30, status = 'active'
WHERE class_slug = 'organisational-dynamics';

UPDATE wp_sa_classes
SET sort_order = 40, status = 'active'
WHERE class_slug = 'complexity';

UPDATE wp_sa_classes
SET sort_order = 50, status = 'active'
WHERE class_slug = 'product-management';

UPDATE wp_sa_classes
SET sort_order = 60, status = 'active'
WHERE class_slug = 'project-management';

UPDATE wp_sa_classes
SET sort_order = 70, status = 'active'
WHERE class_slug IN ('nhs-specific', 'nhs');

UPDATE wp_sa_classes
SET sort_order = 80, status = 'active'
WHERE class_slug = 'strategy';

UPDATE wp_sa_classes
SET sort_order = 90, status = 'active'
WHERE class_slug = 'sustainability';

UPDATE wp_sa_classes
SET sort_order = 100, status = 'active'
WHERE class_slug IN ('finance-economic-viability', 'finance');

UPDATE wp_sa_classes
SET sort_order = 110, status = 'active'
WHERE class_slug = 'capital-allocation';

UPDATE wp_sa_classes
SET sort_order = 120, status = 'active'
WHERE class_slug = 'innovation';

UPDATE wp_sa_classes
SET sort_order = 130, status = 'active'
WHERE class_slug = 'change-transformation';

UPDATE wp_sa_classes
SET sort_order = 140, status = 'active'
WHERE class_slug = 'leadership';

UPDATE wp_sa_classes
SET sort_order = 150, status = 'active'
WHERE class_slug = 'decision-making';

UPDATE wp_sa_classes
SET sort_order = 160, status = 'active'
WHERE class_slug = 'governance';

UPDATE wp_sa_classes
SET sort_order = 170, status = 'active'
WHERE class_slug = 'risk';

UPDATE wp_sa_classes
SET sort_order = 180, status = 'active'
WHERE class_slug = 'power';

UPDATE wp_sa_classes
SET sort_order = 190, status = 'active'
WHERE class_slug = 'capability-skills';

UPDATE wp_sa_classes
SET sort_order = 200, status = 'active'
WHERE class_slug = 'learning';

UPDATE wp_sa_classes
SET sort_order = 210, status = 'active'
WHERE class_slug = 'knowledge-information';

UPDATE wp_sa_classes
SET sort_order = 220, status = 'active'
WHERE class_slug = 'delivery';

UPDATE wp_sa_classes
SET sort_order = 230, status = 'active'
WHERE class_slug = 'communication';

UPDATE wp_sa_classes
SET sort_order = 240, status = 'active'
WHERE class_slug = 'relationships';

UPDATE wp_sa_classes
SET sort_order = 250, status = 'active'
WHERE class_slug = 'culture';

UPDATE wp_sa_classes
SET sort_order = 260, status = 'active'
WHERE class_slug = 'ethics-trust';

UPDATE wp_sa_classes
SET sort_order = 270, status = 'active'
WHERE class_slug = 'meaning-purpose';

UPDATE wp_sa_classes
SET sort_order = 280, status = 'active'
WHERE class_slug = 'health-safety';

COMMIT;