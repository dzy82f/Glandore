(function () {
    document.addEventListener('DOMContentLoaded', function () {
        var container = document.getElementById('cop-dialogue');
        var form      = document.getElementById('cop-dialogue-form');
        var input     = document.getElementById('cop-dialogue-input');

        if (!container || !form || !input) {
            return;
        }

        var ajaxUrl = container.getAttribute('data-ajax-url');
        var nonce   = container.getAttribute('data-nonce');

        var step = 0; // 0=intro, 1=classifications, 2=agreements, 3=finalise

        function addMessage(role, text) {
            var div = document.createElement('div');
            div.className = 'cop-msg cop-msg-' + role;
            div.innerText = text;
            container.appendChild(div);
            container.scrollTop = container.scrollHeight;
        }

        function showIntroQuestion() {
            addMessage(
                'ai',
                'As this Community has never heard of you before, how would you introduce yourself here – who you are, what you work on, and what you are currently trying to figure out?'
            );
            step = 0;
            input.value = '';
            input.placeholder = 'Type your introduction…';
            input.focus();
        }

        function showClassificationQuestion() {
            addMessage(
                'ai',
                'Thank you. To help the Community place your work, please choose roughly where you mainly work, which broad industry fits you best, and how you would describe your current role.'
            );

            var extra = form.querySelector('.cop-dialogue-extra');
            extra.innerHTML = '';
            extra.style.display = 'block';

            var regionSelect = document.createElement('select');
            regionSelect.name = 'location_region';
            regionSelect.innerHTML =
                '<option value="">Where do you mainly work?</option>' +
                '<option value="africa">Africa</option>' +
                '<option value="americas">Americas</option>' +
                '<option value="asia">Asia</option>' +
                '<option value="europe">Europe</option>' +
                '<option value="oceania">Oceania</option>' +
                '<option value="global">Global / multiple regions</option>' +
                '<option value="prefer_not_to_say">Prefer not to say</option>';

            var industrySelect = document.createElement('select');
            industrySelect.name = 'industry_section';
            industrySelect.innerHTML =
                '<option value="">Which broad industry best fits your work?</option>' +
                '<option value="A">Agriculture, forestry and fishing</option>' +
                '<option value="B">Mining and quarrying</option>' +
                '<option value="C">Manufacturing</option>' +
                '<option value="D">Electricity, gas, steam and air conditioning supply</option>' +
                '<option value="E">Water supply; sewerage, waste management and remediation</option>' +
                '<option value="F">Construction</option>' +
                '<option value="G">Wholesale and retail trade; repair of motor vehicles and motorcycles</option>' +
                '<option value="H">Transportation and storage</option>' +
                '<option value="I">Accommodation and food service activities</option>' +
                '<option value="J">Information and communication</option>' +
                '<option value="K">Financial and insurance activities</option>' +
                '<option value="L">Real estate activities</option>' +
                '<option value="M">Professional, scientific and technical activities</option>' +
                '<option value="N">Administrative and support service activities</option>' +
                '<option value="O">Public administration and defence; compulsory social security</option>' +
                '<option value="P">Education</option>' +
                '<option value="Q">Human health and social work activities</option>' +
                '<option value="R">Arts, entertainment and recreation</option>' +
                '<option value="S">Other service activities</option>' +
                '<option value="T">Households as employers / domestic production</option>' +
                '<option value="U">Extraterritorial organisations and bodies</option>' +
                '<option value="X">Mixed / does not fit easily</option>' +
                '<option value="Z">Prefer not to say</option>';

            var roleSelect = document.createElement('select');
            roleSelect.name = 'role_category';
            roleSelect.innerHTML =
                '<option value="">Which best describes your current role?</option>' +
                '<option value="senior_leader">Senior leader / executive</option>' +
                '<option value="manager">Manager / team lead</option>' +
                '<option value="practitioner">Practitioner / frontline professional</option>' +
                '<option value="consultant">Consultant / advisor</option>' +
                '<option value="researcher">Researcher / academic</option>' +
                '<option value="student">Student</option>' +
                '<option value="other">Other</option>';

            extra.appendChild(regionSelect);
            extra.appendChild(industrySelect);
            extra.appendChild(roleSelect);

            step = 1;
            input.value = '';
            input.placeholder = 'Click Send once you have chosen…';
        }

        function showAgreements() {
            addMessage(
                'ai',
                'Finally, there are a couple of simple agreements about how your profile is used and how this Community works.'
            );

            var extra = form.querySelector('.cop-dialogue-extra');
            extra.innerHTML = '';
            extra.style.display = 'block';

            var visibleLabel = document.createElement('label');
            var visibleCb    = document.createElement('input');
            visibleCb.type   = 'checkbox';
            visibleCb.name   = 'profile_visible';
            visibleCb.value  = '1';
            visibleLabel.appendChild(visibleCb);
            visibleLabel.appendChild(document.createTextNode(
                ' I’m comfortable for this profile (excluding my email address) to be visible to other members.'
            ));

            var ethosLabel = document.createElement('label');
            var ethosCb    = document.createElement('input');
            ethosCb.type   = 'checkbox';
            ethosCb.name   = 'ethos_accepted';
            ethosCb.value  = '1';
            ethosLabel.appendChild(ethosCb);
            ethosLabel.appendChild(document.createTextNode(
                ' I will treat this space as a place for thoughtful, good-faith conversation, not quick takes or advertising.'
            ));

            extra.appendChild(visibleLabel);
            extra.appendChild(document.createElement('br'));
            extra.appendChild(ethosLabel);

            step = 2;
            input.value = '';
            input.placeholder = 'Click Send when you have read and agreed…';
        }

        function postStep(data, callback) {
            data = data || {};
            data.action = 'glandore_cop_save_step';
            data.nonce  = nonce;

            var xhr = new XMLHttpRequest();
            xhr.open('POST', ajaxUrl, true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
            xhr.onload = function () {
                var resp;
                try {
                    resp = JSON.parse(xhr.responseText);
                } catch (e) {
                    resp = { success: false, data: { message: 'Bad JSON' } };
                }
                callback(resp);
            };

            var encoded = Object.keys(data).map(function (k) {
                return encodeURIComponent(k) + '=' + encodeURIComponent(data[k]);
            }).join('&');

            xhr.send(encoded);
        }

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            if (step === 0) {
                var text = input.value.trim();
                if (!text) {
                    return;
                }
                addMessage('user', text);

                postStep(
                    { step: 'intro', intro: text },
                    function () {
                        showClassificationQuestion();
                    }
                );

            } else if (step === 1) {
                var extra = form.querySelector('.cop-dialogue-extra');
                var region = extra.querySelector('select[name="location_region"]').value;
                var industry = extra.querySelector('select[name="industry_section"]').value;
                var role = extra.querySelector('select[name="role_category"]').value;

                postStep(
                    {
                        step: 'classifications',
                        location_region: region,
                        industry_section: industry,
                        role_category: role
                    },
                    function () {
                        showAgreements();
                    }
                );

            } else if (step === 2) {
                var extra = form.querySelector('.cop-dialogue-extra');
                var visible = extra.querySelector('input[name="profile_visible"]').checked ? '1' : '';
                var ethos   = extra.querySelector('input[name="ethos_accepted"]').checked ? '1' : '';

                postStep(
                    {
                        step: 'agreements',
                        profile_visible: visible,
                        ethos_accepted: ethos
                    },
                    function () {
                        // Final evaluation.
                        postStep(
                            { step: 'finalise' },
                            function (resp) {
                                if (!resp || !resp.success) {
                                    addMessage('ai', 'Something went wrong saving your profile. Please try again.');
                                    return;
                                }
                                var status = resp.data.status;
                                var expl   = resp.data.explanation || '';

                                if (status === 'pass') {
                                    addMessage('ai', 'Thank you. I have enough to welcome you in.');
									addMessage('ai', 'Outcome: PASS. ' + expl);
                                } else {
                                    addMessage('ai', 'Thank you. I need a little more from you before we open posting.');
									addMessage('ai', 'Outcome: FAIL. ' + expl);
                                }

                                input.disabled = true;
                                var extraBox = form.querySelector('.cop-dialogue-extra');
                                if (extraBox) {
                                    extraBox.style.display = 'none';
                                }
                            }
                        );
                    }
                );
            }
        });

        showIntroQuestion();
    });
})();
