import React from 'react';

import styles from './index.module.scss';

const Component = () => {
  return (
    <div className={styles.integrasiSystemTamba}>
      <div className={styles.rectangle18}>
        <div className={styles.rectangle13}>
          <img
            src="../image/mkmako50-4tq37g2.png"
            className={styles.logoPertaminaGasNega}
          />
          <div className={styles.autoWrapper}>
            <div className={styles.lineMdHomeTwotone}>
              <img src="../image/mkmako4y-byfgzd4.svg" className={styles.vector} />
              <img src="../image/mkmako4y-ropm9l5.svg" className={styles.group} />
              <img
                src="../image/mkmako4y-88ah1il.svg"
                className={styles.materialSymbolsHomeR}
              />
            </div>
            <p className={styles.beranda}>Beranda</p>
          </div>
          <div className={styles.autoWrapper2}>
            <img
              src="../image/mkmako4y-phcmv13.svg"
              className={styles.materialSymbolsHisto}
            />
            <p className={styles.history}>History</p>
          </div>
          <div className={styles.rectangle16}>
            <p className={styles.tambahModulSystem}>Tambah Modul / System</p>
            <div className={styles.rectangle17}>
              <img src="../image/mkmako4y-brxmbpz.svg" className={styles.vector2} />
              <p className={styles.integrasiSistem}>Integrasi Sistem</p>
            </div>
          </div>
        </div>
        <div className={styles.autoWrapper5}>
          <div className={styles.rectangle6}>
            <img src="../image/mkmako4y-4aqfg63.svg" className={styles.group2} />
            <div className={styles.autoWrapper3}>
              <p className={styles.dashboardIntegrasiSi}>
                Dashboard → Integrasi Sistem
              </p>
              <img src="../image/mkmako4y-a9u6r17.svg" className={styles.vector3} />
              <p className={styles.supervisor}>Supervisor</p>
            </div>
          </div>
          <div className={styles.rectangle42}>
            <div className={styles.autoWrapper4}>
              <img
                src="../image/mkmako4y-ebpzxbc.svg"
                className={styles.materialSymbolsHisto}
              />
              <p className={styles.tambahModulAplikasi}>Tambah Modul Aplikasi</p>
            </div>
            <div className={styles.rectangle47}>
              <p className={styles.namaModulAplikasi}>Nama Modul / Aplikasi</p>
              <div className={styles.roundedRectangle}>
                <p className={styles.contohBukuSakuDigita}>
                  Contoh : Buku Saku Digital
                </p>
              </div>
              <p className={styles.deskripsiSingkat}>Deskripsi Singkat</p>
              <div className={styles.roundedRectangle2}>
                <p className={styles.contohBukuSakuDigita}>
                  Contoh: "Panduan teknis lapangan untuk QAQC." (Maks 100-150
                  karakter).
                </p>
              </div>
              <p className={styles.deskripsiSingkat}>Kategori</p>
              <p className={styles.targetUrlEndpoint}>Target URL / Endpoint</p>
              <div className={styles.roundedRectangle3}>
                <p className={styles.contohInternalEkster}>
                  Contoh Internal / Eksternal: /buku-saku atau
                  https://dashboard-pertamina.com
                </p>
              </div>
              <p className={styles.deskripsiSingkat}>Tipe Tab</p>
              <div className={styles.roundedRectangle4}>
                <p className={styles.contohBukuSakuDigita}>Pilih tipe tab...</p>
                <p className={styles.newTabBlank}>New tab (Blank)</p>
              </div>
              <img
                src="../image/mkmako4y-0omafwu.svg"
                className={styles.ionSwitch}
              />
              <div className={styles.rectangle45}>
                <p className={styles.tambahModul}>Tambah Modul</p>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div className={styles.autoWrapper8}>
        <p className={styles.beranda2}>Beranda</p>
        <p className={styles.history2}>History</p>
        <div className={styles.autoWrapper7}>
          <img
            src="../image/mkmako4y-rxh7hpz.png"
            className={styles.gridiconsDropdown}
          />
          <div className={styles.group12}>
            <div className={styles.autoWrapper6}>
              <div className={styles.roundedRectangle5}>
                <p className={styles.projectManagementOff}>
                  Project Management Office
                </p>
              </div>
              <img
                src="../image/mkmako4y-7nl0clj.svg"
                className={styles.gridiconsDropdown2}
              />
            </div>
            <div className={styles.rectangle49}>
              <p className={styles.projectManagementOff}>Current Tab</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

export default Component;
