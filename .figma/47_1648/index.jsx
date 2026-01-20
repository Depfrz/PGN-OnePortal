import React from 'react';

import styles from './index.module.scss';

const Component = () => {
  return (
    <div className={styles.adminMengklikLogout}>
      <div className={styles.rectangle182}>
        <div className={styles.rectangle6}>
          <div className={styles.ionNotifcations}>
            <img src="../image/mkmako7d-7q1e58o.svg" className={styles.frame1} />
          </div>
          <p className={styles.admin}>Admin</p>
          <img src="../image/mkmako7d-8vwxj3i.svg" className={styles.group} />
        </div>
        <div className={styles.group11}>
          <div className={styles.rectangle16}>
            <div className={styles.autoWrapper}>
              <p className={styles.selamatDatangAdmin}>Selamat Datang, Admin.</p>
              <div className={styles.rectangle18}>
                <img
                  src="../image/mkmako7d-uay7fn2.svg"
                  className={styles.mdiBookOpenVariantOu}
                />
                <p className={styles.bukuSaku}>Buku Saku</p>
              </div>
              <div className={styles.rectangle36}>
                <p className={styles.penjelasanDetailMeng}>
                  Penjelasan Detail Mengenai Buku Saku
                </p>
              </div>
            </div>
            <div className={styles.rectangle39}>
              <p className={styles.listPengawasYangSeda}>
                List Pengawas yang sedang aktif
                <br />
              </p>
            </div>
            <div className={styles.autoWrapper2}>
              <div className={styles.rectangle41}>
                <p className={styles.riwayatTerbaru}>Riwayat Terbaru</p>
              </div>
              <div className={styles.rectangle40}>
                <p className={styles.listPengawasYangSeda}>
                  Riwayat Terbaru Mengenai Tambahan Model/Edit/Hapus Beserta
                  informasi waktu
                  <br />
                  <br />
                </p>
              </div>
            </div>
            <div className={styles.rectangle26}>
              <p className={styles.apakahAndaYakinIngin}>
                Apakah Anda Yakin Ingin Logout?
              </p>
              <div className={styles.autoWrapper3}>
                <div className={styles.rectangle43}>
                  <p className={styles.riwayatTerbaru}>Ya</p>
                </div>
                <div className={styles.rectangle432}>
                  <p className={styles.tidak}>&nbsp;Tidak</p>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div className={styles.rectangle42}>
          <div className={styles.autoWrapper4}>
            <img
              src="../image/mkmako7d-4eb3pqz.svg"
              className={styles.mdiSettings}
            />
            <img src="../image/mkmako7d-jat06c3.svg" className={styles.mdiLogout} />
          </div>
          <div className={styles.autoWrapper5}>
            <p className={styles.profilAkun}>Profil Akun</p>
            <p className={styles.logout}>Logout</p>
          </div>
        </div>
      </div>
      <div className={styles.rectangle13}>
        <img
          src="../image/mkmako7j-wza1dm3.png"
          className={styles.logoPertaminaGasNega}
        />
        <div className={styles.rectangle162}>
          <div className={styles.lineMdHomeTwotone}>
            <img src="../image/mkmako7d-j2zzclk.svg" className={styles.vector} />
            <img src="../image/mkmako7d-130wqj4.svg" className={styles.group2} />
            <img
              src="../image/mkmako7d-gbqyqxj.svg"
              className={styles.materialSymbolsHomeR}
            />
          </div>
          <p className={styles.beranda}>Beranda</p>
        </div>
        <div className={styles.autoWrapper7}>
          <div className={styles.mdiAccountEyeOutline}>
            <img src="../image/mkmako7d-x5ppipd.svg" className={styles.vector2} />
          </div>
          <div className={styles.autoWrapper6}>
            <p className={styles.history}>History</p>
            <p className={styles.history2}>History</p>
          </div>
        </div>
        <div className={styles.autoWrapper8}>
          <img src="../image/mkmako7d-8jytxt5.svg" className={styles.group3} />
          <p className={styles.managamentUser}>Managament User</p>
        </div>
        <div className={styles.autoWrapper9}>
          <img src="../image/mkmako7d-ylz7eyc.svg" className={styles.vector3} />
          <p className={styles.managamentUser}>Integrasi Sistem</p>
        </div>
      </div>
      <p className={styles.beranda2}>Beranda</p>
      <p className={styles.managamentUser2}>Managament User</p>
    </div>
  );
}

export default Component;
